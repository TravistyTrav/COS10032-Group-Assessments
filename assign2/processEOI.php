<?php
require_once("util/functions.php");
require_once("settings.php");

// Start session early before any output
session_start();

// Connect
$conn = db_connect_or_exit();

// Don't allow direct access, redirect to error page with message
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['form_errors'] = array("Invalid request method. Please submit the form again.");
  header('Location: error.php');
  exit;
}

// ---- Ensure table exists ----
$createSql = "
CREATE TABLE IF NOT EXISTS `eoi` (
  `EOInumber` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `JobRefNo`  VARCHAR(5)  NOT NULL,
  `FirstName` VARCHAR(20) NOT NULL,
  `LastName`  VARCHAR(20) NOT NULL,
  `DOB`       DATE        NOT NULL,
  `Gender`    VARCHAR(16) NOT NULL,
  `Street`    VARCHAR(100) NOT NULL,
  `City`      VARCHAR(40) NOT NULL,
  `State`     VARCHAR(10) NOT NULL,
  `PostCode`  CHAR(4)     NOT NULL,
  `Email`     VARCHAR(255) NOT NULL,
  `Phone`     VARCHAR(32) NOT NULL,
  `Skills`    VARCHAR(255) NOT NULL,
  `OtherSkill` TEXT,
  `Status`    ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
  `CreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`EOInumber`),
  KEY `idx_jobref` (`JobRefNo`),
  KEY `idx_name` (`LastName`, `FirstName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (!mysqli_query($conn, $createSql)) {
  $_SESSION['form_errors'] = array("Server error creating table. Please try again later.");
  header('Location: error.php');
  exit;
}

//  Sanitize
$fields = array(
  // Reference number
  'reference' => clean(isset($_POST['reference']) ? $_POST['reference'] : ''),

  // About You
  'firstname' => clean(isset($_POST['firstname']) ? $_POST['firstname'] : ''),
  'lastname'  => clean(isset($_POST['lastname']) ? $_POST['lastname'] : ''),
  'dob'       => clean(isset($_POST['dob']) ? $_POST['dob'] : ''),

  // Gender (single field now)
  'gender' => isset($_POST['gender']) ? clean($_POST['gender']) : '',

  // Address
  'street'   => clean(isset($_POST['street']) ? $_POST['street'] : ''),
  'city'     => clean(isset($_POST['city']) ? $_POST['city'] : ''),
  'state'    => clean(isset($_POST['state']) ? $_POST['state'] : ''),
  'postcode' => clean(isset($_POST['postcode']) ? $_POST['postcode'] : ''),

  // Contact
  'email' => clean(isset($_POST['email']) ? $_POST['email'] : ''),
  'phone' => clean(isset($_POST['phone']) ? $_POST['phone'] : ''),

  // Skills
  'skill_html'  => isset($_POST['skill_html']) ? 'html'  : '',
  'skill_css'   => isset($_POST['skill_css']) ? 'css'   : '',
  'skill_js'    => isset($_POST['skill_js'])  ? 'javascript' : '',
  'skill_php'   => isset($_POST['skill_php']) ? 'php'   : '',
  'skill_mysql' => isset($_POST['skill_mysql']) ? 'mysql' : '',
  'skill_other' => isset($_POST['skill_other']) ? 'other' : '',
  'other'       => clean(isset($_POST['other']) ? $_POST['other'] : ''),
);

$errors = array();

// Validate Required
assert_required($fields['reference'], 'Job Ref No.', $errors);
assert_required($fields['firstname'], 'First Name', $errors);
assert_required($fields['lastname'], 'Last Name', $errors);
assert_required($fields['dob'], 'Date of Birth', $errors);

// Gender Required
assert_required($fields['gender'], 'Gender', $errors);
$gender = $fields['gender'];

assert_required($fields['street'], 'Street', $errors);
assert_required($fields['city'], 'City', $errors);
assert_required($fields['state'], 'State', $errors);
assert_required($fields['postcode'], 'Post Code', $errors);
assert_required($fields['email'], 'Email', $errors);
assert_required($fields['phone'], 'Phone', $errors);

// Field Specific Validation

// Job reference: exactly 5 alphanumeric
if ($fields['reference'] !== '') {
  assert_regex(
    $fields['reference'],
    '/^[A-Za-z0-9]{5}$/',
    "Job Ref No. must be exactly 5 letters/numbers (e.g., ABC12).",
    $errors
  );
}

// Names: letters & spaces, max 20
if ($fields['firstname'] !== '') {
  assert_regex(
    $fields['firstname'],
    '/^[A-Za-z ]{1,20}$/',
    "First Name can only contain letters and spaces (max 20).",
    $errors
  );
}
if ($fields['lastname'] !== '') {
  assert_regex(
    $fields['lastname'],
    '/^[A-Za-z ]{1,20}$/',
    "Last Name can only contain letters and spaces (max 20).",
    $errors
  );
}

// DOB: dd/mm/yyyy + real date
if ($fields['dob'] !== '') {
  assert_dob($fields['dob'], $errors);
}

// City & Street: mirror HTML min/max lengths
if ($fields['street'] !== '' && (strlen($fields['street']) < 8 || strlen($fields['street']) > 40)) {
  $errors[] = "Street must be between 8 and 40 characters.";
}
if ($fields['city'] !== '' && (strlen($fields['city']) < 8 || strlen($fields['city']) > 40)) {
  $errors[] = "City must be between 8 and 40 characters.";
}

// State: one of allowed codes
$valid_states = array('vic','nsw','qld','nt','wa','sa','tas','act');
if ($fields['state'] !== '' && !in_array(strtolower($fields['state']), $valid_states, true)) {
  $errors[] = "Please select a valid State.";
}

// Postcode: 4 digits
if ($fields['postcode'] !== '') {
  assert_regex(
    $fields['postcode'],
    '/^[0-9]{4}$/',
    "Post Code must be exactly 4 digits (e.g., 3000).",
    $errors
  );
}

// Email:
if ($fields['email'] !== '' && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
  $errors[] = "Please enter a valid email address (e.g., example@domain.com).";
}

// Phone: AU formats
if ($fields['phone'] !== '') {
  $phone_normalized = preg_replace('/[\s\-\(\)]+/', '', $fields['phone']);
  $au_phone_ok =
    preg_match('/^(0[23478]\d{8})$/', $phone_normalized) ||   // 0X + 8 digits
    preg_match('/^\+61[23478]\d{8}$/', $phone_normalized);    // +61X + 8 digits
  if (!$au_phone_ok) {
    $errors[] = "Please enter a valid Australian phone number (e.g., 0412345678 or +61412345678).";
  }
}

// Skills: if "Other" checked, description required
if ($fields['skill_other'] === 'other') {
  if ($fields['other'] === '' || strlen($fields['other']) < 5) {
    $errors[] = "Please describe your Other skill (at least 5 characters).";
  }
}

// On Error redirect to error.php
if (!empty($errors)) {
  $_SESSION['form_errors'] = $errors;
  $_SESSION['old_input'] = $fields; // keep inputs for re-populating if needed
  header('Location: error.php');
  exit;
}

// Build clean data for display + DB
$skillsArray = array_values(array_filter(array(
  $fields['skill_html'],
  $fields['skill_css'],
  $fields['skill_js'],
  $fields['skill_php'],
  $fields['skill_mysql']
)));
$skillsCsv = implode(',', $skillsArray);
$otherSkill = ($fields['skill_other'] === 'other') ? $fields['other'] : '';

$dob_mysql = dob_to_mysql($fields['dob']);

// ---- Insert to DB (prepared statement) ----
$ins = mysqli_prepare(
  $conn,
  "INSERT INTO eoi
   (JobRefNo, FirstName, LastName, DOB, Gender, Street, City, State, PostCode, Email, Phone, Skills, OtherSkill, Status)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')"
);

if (!$ins) {
  $_SESSION['form_errors'] = array("Server error preparing insert. Please try again later.");
  header('Location: error.php');
  exit;
}

mysqli_stmt_bind_param(
  $ins,
  'ssssssssssss', // 12 strings before Status
  $fields['reference'],
  $fields['firstname'],
  $fields['lastname'],
  $dob_mysql,
  $gender,
  $fields['street'],
  $fields['city'],
  $fields['state'],
  $fields['postcode'],
  $fields['email'],
  $fields['phone'],
  $skillsCsv,
  $otherSkill
);

// Note: we bound 13 variables but declared 12 's'? Let's align it:
mysqli_stmt_close($ins);

// Re-prepare with the correct count
$ins = mysqli_prepare(
  $conn,
  "INSERT INTO eoi
   (JobRefNo, FirstName, LastName, DOB, Gender, Street, City, State, PostCode, Email, Phone, Skills, OtherSkill, Status)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')"
);
mysqli_stmt_bind_param(
  $ins,
  'ssssssssssssss', // 14 placeholders? No, Status is literal; we have 13 values -> 13 's'
  $fields['reference'],
  $fields['firstname'],
  $fields['lastname'],
  $dob_mysql,
  $gender,
  $fields['street'],
  $fields['city'],
  $fields['state'],
  $fields['postcode'],
  $fields['email'],
  $fields['phone'],
  $skillsCsv,
  $otherSkill
);

/* Final correct version */
mysqli_stmt_close($ins);
$ins = mysqli_prepare(
  $conn,
  "INSERT INTO eoi
   (JobRefNo, FirstName, LastName, DOB, Gender, Street, City, State, PostCode, Email, Phone, Skills, OtherSkill, Status)
   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')"
);
mysqli_stmt_bind_param(
  $ins,
  'sssssssssssss', // 13 values to bind (Status is fixed) -> 13 's'
  $fields['reference'],
  $fields['firstname'],
  $fields['lastname'],
  $dob_mysql,
  $gender,
  $fields['street'],
  $fields['city'],
  $fields['state'],
  $fields['postcode'],
  $fields['email'],
  $fields['phone'],
  $skillsCsv,
  $otherSkill
);

if (!mysqli_stmt_execute($ins)) {
  $_SESSION['form_errors'] = array("Server error saving your submission. Please try again later.");
  header('Location: error.php');
  exit;
}

// Get the inserted EOI number (optional)
$inserted_id = mysqli_insert_id($conn);

// Prepare data for success page
$cleanData = array(
  'reference' => $fields['reference'],
  'firstname' => $fields['firstname'],
  'lastname'  => $fields['lastname'],
  'dob'       => $fields['dob'],
  'gender'    => $gender,
  'street'    => $fields['street'],
  'city'      => $fields['city'],
  'state'     => strtoupper($fields['state']),
  'postcode'  => $fields['postcode'],
  'email'     => $fields['email'],
  'phone'     => $fields['phone'],
  'skills'    => array_values(array_filter(array_merge($skillsArray, ($otherSkill !== '' ? array('other: ' . $otherSkill) : array())))),
  'eoi_number'=> $inserted_id,
);

// Store & redirect
$_SESSION['form_data'] = $cleanData;

mysqli_stmt_close($ins);
mysqli_close($conn);

header('Location: success.php');
exit;
