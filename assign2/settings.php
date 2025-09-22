<?php
$host   = "feenix-mariadb.swin.edu.au";
$user   = "s106130521";      
$pwd    = "111194";      
$sql_db = "s106130521_db";   
function db_connect_or_exit() {
  global $host, $user, $pwd, $sql_db;
  $conn = @mysqli_connect($host, $user, $pwd, $sql_db);
  if (!$conn) {
    http_response_code(500);
    echo "<!doctype html><meta charset='utf-8'><h2>Database connection failed</h2>";
    echo "<p>Please try again later. If the problem persists, contact your tutor.</p>";
    exit();
  }
  mysqli_set_charset($conn, "utf8mb4");
  return $conn;
}
?>