<?php
// processEOI.php
require_once 'settings.php';
require_once 'functions.inc.php';

// Block direct GET access
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
  header("Location: apply.php");
  exit();
}

$conn = db_connect_or_exit();

// Create table if not exists
// must create status enum in sqlitestudio - query is in mysql syntax
$create = "CREATE TABLE IF NOT EXISTS EOI (
  EOInumber INT AUTO_INCREMENT PRIMARY KEY,
  job_ref CHAR(5) NOT NULL,
  first_name VARCHAR(20) NOT NULL,
  last_name  VARCHAR(20) NOT NULL,
  dob        DATE NOT NULL,
  gender     VARCHAR(20) NOT NULL,
  street     VARCHAR(40) NOT NULL,
  suburb     VARCHAR(40) NOT NULL,
  state      CHAR(3) NOT NULL,
  postcode   CHAR(4) NOT NULL,
  email      VARCHAR(128) NOT NULL,
  phone      VARCHAR(16) NOT NULL,
  skill1     VARCHAR(32) NULL,
  skill2     VARCHAR(32) NULL,
  skill3     VARCHAR(32) NULL,
  skill4     VARCHAR(32) NULL,
  other_skills TEXT NULL,
  status     ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $create);

// Sanitize
$job_ref = clean_input($_POST['job_ref'] ?? "");
$first_name = clean_input($_POST['first_name'] ?? "");
$last_name = clean_input($_POST['last_name'] ?? "");
$dob_in = clean_input($_POST['dob'] ?? "");
$gender  = clean_input($_POST['gender'] ?? "");
$street  = clean_input($_POST['street'] ?? "");
$city  = clean_input($_POST['suburb'] ?? "");
$state   = clean_input($_POST['state'] ?? "");
$postcode   = clean_input($_POST['postcode'] ?? "");
$email   = clean_input($_POST['email'] ?? "");
$ph_number   = clean_input($_POST['phone'] ?? "");
$skills  = isset($_POST['skill1']) ? clean_input($_POST['skill1']) : NULL;
$other_skills = isset($_POST['other_skills_chk']) ? true : false;

// Validate
$errors = [];

if (!is_alnum_exact($job_ref, 5)) $errors[] = "Job reference must be exactly 5 alphanumeric characters.";
if (!is_alpha($first, 20)) $errors[] = "First name must be alpha only (max 20).";
if (!is_alpha($last, 20)) $errors[]  = "Last name must be alpha only (max 20).";

$age = dob_to_age($dob_in);
if ($age < 0) $errors[] = "Date of birth must be in dd/mm/yyyy format.";
else if ($age < 15 || $age > 80) $errors[] = "Age must be between 15 and 80.";

if (empty($gender)) $errors[] = "Gender must be selected.";
if (strlen($street) == 0 || strlen($street) > 40) $errors[] = "Street must be 1–40 chars.";
if (strlen($suburb) == 0 || strlen($suburb) > 40) $errors[] = "Suburb must be 1–40 chars.";
if (!is_state($state)) $errors[] = "State must be one of VIC,NSW,QLD,NT,WA,SA,TAS,ACT.";
if (!is_postcode($pcode)) $errors[] = "Postcode must be exactly 4 digits.";
if (is_postcode($pcode) && is_state($state) && !state_matches_postcode($state, $pcode)) {
  $errors[] = "Postcode does not match the selected state.";
}
if (!valid_email($email)) $errors[] = "Email is invalid.";
if (!valid_phone($phone)) $errors[] = "Phone must be 8–12 digits or spaces.";
if ($other_chk && strlen(trim($other)) === 0) $errors[] = "Other skills description is required when checked.";

if (!empty($errors)) {
  http_response_code(422);
  ?>
  <!doctype html><meta charset="utf-8"><link rel="stylesheet" href="styles/style.css">
  <main class="container">
    <h2>Submission Error</h2>
    <div class="card error">
      <p>Please fix the following:</p>
      <ul>
        <?php foreach ($errors as $e) echo "<li>" . $e . "</li>"; ?>
      </ul>
      <p><a href="apply.php">Go back to the application form</a></p>
    </div>
  </main>
  <?php
  exit();
}

// Convert dob to Y-m-d for MySQL
list($d,$m,$y) = explode("/", $dob_in);
$dob = sprintf("%04d-%02d-%02d", intval($y), intval($m), intval($d));

// Insert (prepared)
$stmt = mysqli_prepare($conn, "INSERT INTO eoi
(job_ref, first_name, last_name, dob, gender, street, city, state, postcode, email, ph_number, skills, other_skills, status)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'New')");
mysqli_stmt_bind_param($stmt, "ssssssssssssssss",
  $job_ref, $first_name, $last_name, $dob, $gender, $street, $city, $state, $postcode, $email, $ph_number,
  $skills, $other_skills, status
);
$ok = mysqli_stmt_execute($stmt);

if (!$ok) {
  http_response_code(500);
  ?>
  <!doctype html><meta charset="utf-8"><link rel="stylesheet" href="styles/style.css">
  <main class="container">
    <h2>We couldn't save your EOI</h2>
    <div class="card error">
      <p>Sorry, something went wrong. Please try again later.</p>
    </div>
  </main>
  <?php
  exit();
}
$eoi_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!doctype html>
<meta charset="utf-8">
<link rel="stylesheet" href="styles/style.css">
<main class="container">
  <h2>Thanks for applying!</h2>
  <div class="card success">
    <p>Your Expression of Interest was received.</p>
    <p><strong>EOInumber:</strong> <?php echo htmlspecialchars($eoi_id); ?></p>
    <p>Status is set to <strong>New</strong>.</p>
    <p><a href="index.php">Return to Home</a></p>
  </div>
</main>
