<?php
// manage.php — HR manager queries
require_once 'settings.php';

$conn = db_connect_or_exit();

// helper to escape HTML
function e($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Ensure table exists (optional safety)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS eoi (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$feedback = "";
$results  = [];

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? "";
  if ($action === 'list_all') {
    $res = mysqli_query($conn, "SELECT * FROM eoi ORDER BY EOInumber DESC");
    $results = $res ? $res : [];
  }
  if ($action === 'list_by_ref') {
    $ref = strtoupper(trim($_POST['ref'] ?? ""));
    $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE job_ref=? ORDER BY EOInumber DESC");
    mysqli_stmt_bind_param($stmt, "s", $ref);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $results = $res ? $res : [];
  }
  if ($action === 'list_by_name') {
    $first = trim($_POST['first'] ?? "");
    $last  = trim($_POST['last'] ?? "");
    if ($first !== "" && $last !== "") {
      $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE first_name=? AND last_name=? ORDER BY EOInumber DESC");
      mysqli_stmt_bind_param($stmt, "ss", $first, $last);
    } elseif ($first !== "") {
      $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE first_name=? ORDER BY EOInumber DESC");
      mysqli_stmt_bind_param($stmt, "s", $first);
    } else {
      $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE last_name=? ORDER BY EOInumber DESC");
      mysqli_stmt_bind_param($stmt, "s", $last);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $results = $res ? $res : [];
  }
  if ($action === 'delete_by_ref') {
    $ref = strtoupper(trim($_POST['ref_del'] ?? ""));
    $stmt = mysqli_prepare($conn, "DELETE FROM eoi WHERE job_ref=?");
    mysqli_stmt_bind_param($stmt, "s", $ref);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    $feedback = $affected . " record(s) deleted for Job Ref " . e($ref) . ".";
  }
  if ($action === 'update_status') {
    $eoi = intval($_POST['eoi_id'] ?? 0);
    $status = $_POST['new_status'] ?? 'New';
    if (!in_array($status, ['New','Current','Final'], true)) $status = 'New';
    $stmt = mysqli_prepare($conn, "UPDATE eoi SET status=? WHERE EOInumber=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $eoi);
    mysqli_stmt_execute($stmt);
    $feedback = "EOInumber " . e($eoi) . " updated to status " . e($status) . ".";
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage EOIs</title>
  <link rel="stylesheet" href="styles/style.css">
</head>
<body>
  <?php include 'header.inc'; ?>
  <?php include 'menu.inc'; ?>
  <main class="container">
    <h2>HR Manager – EOI Console</h2>

    <?php if ($feedback): ?>
      <div class="card info"><p><?php echo $feedback; ?></p></div>
    <?php endif; ?>

    <section class="grid-2">
      <form method="post" class="card">
        <h3>List all EOIs</h3>
        <input type="hidden" name="action" value="list_all">
        <button type="submit">List All</button>
      </form>

      <form method="post" class="card">
        <h3>List by Job Ref</h3>
        <label>Ref <input type="text" name="ref" maxlength="5" required></label>
        <input type="hidden" name="action" value="list_by_ref">
        <button type="submit">List</button>
      </form>

      <form method="post" class="card">
        <h3>List by Applicant</h3>
        <label>First <input type="text" name="first"></label>
        <label>Last <input type="text" name="last"></label>
        <input type="hidden" name="action" value="list_by_name">
        <button type="submit">List</button>
      </form>

      <form method="post" class="card danger">
        <h3>Delete by Job Ref</h3>
        <label>Ref <input type="text" name="ref_del" maxlength="5" required></label>
        <input type="hidden" name="action" value="delete_by_ref">
        <button type="submit" onclick="return confirm('Delete ALL EOIs for this job ref?');">Delete</button>
      </form>

      <form method="post" class="card">
        <h3>Change Status</h3>
        <label>EOInumber <input type="number" name="eoi_id" required></label>
        <label>Status
          <select name="new_status">
            <option>New</option>
            <option>Current</option>
            <option>Final</option>
          </select>
        </label>
        <input type="hidden" name="action" value="update_status">
        <button type="submit">Update</button>
      </form>
    </section>

    <?php if ($results instanceof mysqli_result): ?>
      <section class="card">
        <h3>Results (<?php echo $results->num_rows; ?>)</h3>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>EOI#</th><th>JobRef</th><th>Name</th><th>DOB</th><th>Gender</th>
                <th>Address</th><th>Contact</th><th>Skills</th><th>Status</th><th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $results->fetch_assoc()): ?>
              <tr>
                <td><?php echo e($row['EOInumber']); ?></td>
                <td><?php echo e($row['job_ref']); ?></td>
                <td><?php echo e($row['first_name'] . ' ' . $row['last_name']); ?></td>
                <td><?php echo e($row['dob']); ?></td>
                <td><?php echo e($row['gender']); ?></td>
                <td><?php echo e($row['street'] . ', ' . $row['suburb'] . ' ' . $row['state'] . ' ' . $row['postcode']); ?></td>
                <td><?php echo e($row['email'] . ' / ' . $row['phone']); ?></td>
                <td><?php echo e(implode(', ', array_filter([$row['skill1'],$row['skill2'],$row['skill3'],$row['skill4'], $row['other_skills']]))); ?></td>
                <td><?php echo e($row['status']); ?></td>
                <td><?php echo e($row['created_at']); ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

  </main>
  <?php include 'footer.inc'; ?>
</body>
</html>
