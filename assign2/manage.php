<?php
require_once 'settings.php';

// Connect
$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
if (!$conn) {
    die('Database connection failed.');
}

// Clean input
function clean($data, $conn) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Show table
function showTable($result) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo '<tr>';
    foreach (mysqli_fetch_fields($result) as $f) {
        echo "<th>{$f->name}</th>";
    }
    echo '</tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        foreach ($row as $val) {
            echo '<td>' . htmlspecialchars($val) . '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage EOIs</title>
</head>
<body>
    <h1>HR Manager – Manage EOIs</h1>

    <!-- Forms -->
    <form method="post">
        <fieldset>
            <legend>List All EOIs</legend>
            <button type="submit" name="list_all">Show All</button>
        </fieldset>
    </form>

    <form method="post">
        <fieldset>
            <legend>Search by Job Reference</legend>
            <input type="text" name="jobref" placeholder="e.g. IT001">
            <button type="submit" name="list_by_job">Search</button>
        </fieldset>
    </form>

    <form method="post">
        <fieldset>
            <legend>Search by Applicant</legend>
            <input type="text" name="fname" placeholder="First Name">
            <input type="text" name="lname" placeholder="Last Name">
            <button type="submit" name="list_by_applicant">Search</button>
        </fieldset>
    </form>

    <form method="post">
        <fieldset>
            <legend>Delete by Job Reference</legend>
            <input type="text" name="del_jobref" placeholder="e.g. IT001">
            <button type="submit" name="delete_job">Delete</button>
        </fieldset>
    </form>

    <form method="post">
        <fieldset>
            <legend>Update EOI Status</legend>
            <input type="number" name="eoi_number" placeholder="EOI Number">
            <select name="status">
                <option value="New">New</option>
                <option value="Current">Current</option>
                <option value="Final">Final</option>
            </select>
            <button type="submit" name="update_status">Update</button>
        </fieldset>
    </form>

    <hr>

    <!-- Actions / Results -->
    <?php
    if (isset($_POST['list_all'])) {
        $res = mysqli_query($conn, 'SELECT * FROM eoi');
        echo ($res && mysqli_num_rows($res) > 0) ? showTable($res) : 'No EOIs found.';
    }

    if (isset($_POST['list_by_job'])) {
        $job = clean($_POST['jobref'], $conn);
        $res = mysqli_query($conn, "SELECT * FROM eoi WHERE JobRefNo='$job'");
        echo ($res && mysqli_num_rows($res) > 0) ? showTable($res) : "No EOIs for $job.";
    }

    if (isset($_POST['list_by_applicant'])) {
        $conds = array();
        if (!empty($_POST['fname'])) {
            $conds[] = "FirstName='" . clean($_POST['fname'], $conn) . "'";
        }
        if (!empty($_POST['lname'])) {
            $conds[] = "LastName='" . clean($_POST['lname'], $conn) . "'";
        }

        if ($conds) {
            $sql = 'SELECT * FROM eoi WHERE ' . implode(' AND ', $conds);
            $res = mysqli_query($conn, $sql);
            echo ($res && mysqli_num_rows($res) > 0) ? showTable($res) : 'No EOIs found.';
        } else {
            echo 'Enter first and/or last name.';
        }
    }

    if (isset($_POST['delete_job'])) {
        $job  = clean($_POST['del_jobref'], $conn);
        $done = mysqli_query($conn, "DELETE FROM eoi WHERE JobRefNo='$job'");
        echo $done ? "Deleted EOIs for $job." : 'Nothing deleted.';
    }

    if (isset($_POST['update_status'])) {
        $eoi    = (int)$_POST['eoi_number'];
        $status = clean($_POST['status'], $conn);
        $done   = mysqli_query($conn, "UPDATE eoi SET Status='$status' WHERE EOInumber=$eoi");
        echo ($done && mysqli_affected_rows($conn) > 0)
            ? "EOI #$eoi updated to $status."
            : 'Update failed.';
    }

    mysqli_close($conn);
    ?>
</body>
</html>
