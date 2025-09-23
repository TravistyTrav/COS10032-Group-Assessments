<?php
/*
 Sanitize a single value:
 - trim whitespace
 - strip slashes
 - convert HTML control chars
*/
function clean($value) {
  return htmlspecialchars(stripslashes(trim((string)$value)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Validate a regex with a message on failure
function assert_regex($value, $pattern, $message, &$errors) {
  if (!preg_match($pattern, $value)) {
    $errors[] = $message;
    return false;
  }
  return true;
}

// Validate a required non-empty value after trim
function assert_required($value, $label, &$errors) {
  if ($value === '' || $value === null) {
    $errors[] = "$label is required.";
    return false;
  }
  return true;
}

// Validate date in dd/mm/yyyy and that it's a real calendar date
function assert_dob($dob, &$errors) {
  if (!preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $dob)) {
    $errors[] = "Date of Birth must be in dd/mm/yyyy format.";
    return false;
  }
  list($d, $m, $y) = explode('/', $dob);
  if (!checkdate((int)$m, (int)$d, (int)$y)) {
    $errors[] = "Date of Birth is not a valid calendar date.";
    return false;
  }
  return true;
}

// convert dd/mm/yyyy to YYYY-MM-DD for MySQL
function dob_to_mysql($dob) {
  // assume already validated by assert_dob()
  list($d, $m, $y) = explode('/', $dob);
  $d = (int)$d; $m = (int)$m; $y = (int)$y;
  return sprintf('%04d-%02d-%02d', $y, $m, $d);
}

// Simple escape helper
function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Output for EOI number, padding some 0's on the left if it's a low number
function eoi_fmt($n) {
  return 'EOI-' . str_pad((string)$n, 6, '0', STR_PAD_LEFT);
}

/** Helpers **/
function clean_data($data, $conn) {
  return mysqli_real_escape_string($conn, trim($data));
}

function renderTable($result) {
  if (!$result || mysqli_num_rows($result) === 0) {
    return "<p>No results found.</p>";
  }
  // Header
  $html = "<div class='table-wrapper'><table id='eoi-admin' class='table' border='0' cellspacing='0' cellpadding='0'>";
  $html .= "<thead><tr>";
  foreach (mysqli_fetch_fields($result) as $f) {
    $html .= "<th>" . htmlspecialchars($f->name, ENT_QUOTES, 'UTF-8') . "</th>";
  }
  $html .= "</tr></thead><tbody>";

  // Rows
  while ($row = mysqli_fetch_assoc($result)) {
    $html .= "<tr>";
    foreach ($row as $val) {
      $html .= "<td>" . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . "</td>";
    }
    $html .= "</tr>";
  }
  $html .= "</tbody></table></div>";
  return $html;
}