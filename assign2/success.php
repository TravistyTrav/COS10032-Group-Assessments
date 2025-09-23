<?php
require_once("util/functions.php");

session_start();
$data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : null;

// If no data (directly visited), show a generic page
if (!$data) {
  $title = "Success - Dunder Mifflin I.T. Company";
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <title><?php echo $title; ?></title>

      <meta charset="UTF-8">
      <meta name="viewport"        content="width=device-width, initial-scale=1.0">
      <meta name="description"     content="We link companies with paper products via our SaaS">
      <meta name="keywords"        content="Software, IT Company, IT Employment, Web development">
      <meta name="author"          content="Travis Whitney">
      <link rel="stylesheet"       href="styles/style.css">
  </head>
  <body>
      <?php include 'components/header.inc'; ?>
      <main>
        <section>
          <h2 class="text-center">Submission Error</h2>
          <p class="text-center">We couldn’t find submission details for this session. If you just submitted the form, please try again.</p>
          <p class="text-center"><a href="apply.php">Return to the form</a></p>
          <br>
        </section>
      </main>
      <?php include 'components/footer.inc'; ?>
  </body>
  </html>
  <?php
  exit;
}

// Extract fields for display
$skills = isset($data['skills']) && is_array($data['skills']) ? $data['skills'] : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Success - Dunder Mifflin I.T. Company</title>

    <meta charset="UTF-8">
    <meta name="viewport"        content="width=device-width, initial-scale=1.0">
    <meta name="description"     content="We link companies with paper products via our SaaS">
    <meta name="keywords"        content="Software, IT Company, IT Employment, Web development">
    <meta name="author"          content="Travis Whitney">
    <link rel="stylesheet"       href="styles/style.css">
</head>
<body>
    <?php include 'components/header.inc'; ?>
    <main>
      <section>
        <h2 class="text-center">Thanks! Your Expression of Interest was submitted.</h2>
        <p class="text-center">
          We’ve received your details for <strong>Job Ref: <?= h($data['reference']); ?></strong>.
          Your <strong>EOI # <?= eoi_fmt($data['eoi_number']); ?></strong> has been recorded.
          Our team will be in touch soon.
        </p>
        <br>

        <!-- Summary Table -->
        <div class="table-wrapper">
          <table id="eoi-summary" class="table" aria-describedby="summary-title">
            <tbody>
              <tr>
                <th>EOI Number</th>
                <td><?= eoi_fmt($data['eoi_number']); ?></td>
              </tr>
              <tr>
                <th id="summary-title">First Name</th>
                <td><?= h($data['firstname']); ?></td>
              </tr>
              <tr>
                <th>Last Name</th>
                <td><?= h($data['lastname']); ?></td>
              </tr>
              <tr>
                <th>Date of Birth</th>
                <td><?= h($data['dob']); ?></td>
              </tr>
              <tr>
                <th>Gender</th>
                <td><?= h($data['gender']); ?></td>
              </tr>
              <tr>
                <th>Street</th>
                <td><?= h($data['street']); ?></td>
              </tr>
              <tr>
                <th>City</th>
                <td><?= h($data['city']); ?></td>
              </tr>
              <tr>
                <th>State</th>
                <td><?= h($data['state']); ?></td>
              </tr>
              <tr>
                <th>Post Code</th>
                <td><?= h($data['postcode']); ?></td>
              </tr>
              <tr>
                <th>Email</th>
                <td><?= h($data['email']); ?></td>
              </tr>
              <tr>
                <th>Phone</th>
                <td><?= h($data['phone']); ?></td>
              </tr>
              <tr>
                <th>Skills</th>
                <td>
                  <?php if (!empty($skills)): ?>
                    <ul class="skill-list">
                      <?php foreach ($skills as $s): ?>
                        <li><?= h($s); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <em>Not specified</em>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
    <?php include 'components/footer.inc'; ?>
</body>
</html>
