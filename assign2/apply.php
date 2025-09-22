<?php
require_once 'settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Apply - Dunder Mifflin I.T. Company</title>

    <meta charset="UTF-8">
    <meta name="viewport"        content="width=device-width, initial-scale=1.0">
    <meta name="description"     content="We link companies with paper products via our SaaS">
    <meta name="keywords"        content="Software, IT Company, IT Employment, Web development">
    <meta name="author"          content="Rohan Jouchims">
    <link rel="stylesheet"       href="styles/style.css">
</head>
<body>  
<?php include 'header.inc'; ?>
<main class="apply">
  <div>
    <h1 class="mb-1 text-center">Apply</h1>
    <p class="text-center">
      Interested in joining us? Tell us about you and why you want to work for Dunder Mifflin
    </p>

    <hr class="mb-2 mt-2">

    <h2>Application</h2>
    <p class="text-left">
    Show your interest in Dunder Mifflin by providing your details below
    </p>
  </div>

  <form id="application" method="post" action="http://mercury.swin.edu.au/it000000/formtest.php">
    <!-- Reference number -->
    <fieldset class="mt-1 pb-4">
      <p class="mt-1 mb-1 text-center">
      Please provide the reference number for the job listing you are referencing
      </p>
      <div class="grid-form">
        <div class="mb-1 mt-2">
          <label class="ml-2" for="reference">Job Ref No.</label>
          <input class="ml-2 mt-2" type="text" name="reference" id="reference" placeholder="ABC12" maxlength="5" pattern="[a-zA-Z0-9]{5}" required="required">
        </div>
      </div>
    </fieldset>     


    <!-- About You -->
    <fieldset class="mt-2 pb-4">
      <p class="mt-1 mb-1 text-center ul">About You</p>
      <div class="grid-form">
        <div class="mb-1 mt-2">
            <label for="firstname">First Name</label>
            <input class="float-right mr-2"  type="text" name="firstname" id="firstname" placeholder="John" maxlength="20" pattern="[A-Za-z ]{1,20}" required="required">
        </div>

        <div class="mb-1 mt-2">
            <label for="lastname">Last Name</label>
            <input class="float-right mr-2"  type="text" name="lastname" id="lastname" placeholder="Smith" maxlength="20" pattern="[A-Za-z ]{1,20}" required="required">
        </div>

        <div class="mt-2">
        <label for="dob">Date of Birth</label> 
        <input class="float-right mr-2"  type="text" name="dob" id="dob" placeholder="dd/mm/yyyy" maxlength="10" pattern="(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/\d{4}" required="required">
        </div>
      </div>
    </fieldset>
    
    <!-- Gender Details -->
    <fieldset class="mt-2 pb-4">
      <p class="mt-1 mb-1 text-center ul">What best describes you?</p>
      <div class="grid-form mt-2">
        <div>
          <label for="gender-male">Male</label> 
          <input class="ml-2" type="radio" id="gender-male" name="gender-male" value="gender1" required>
        </div>
        <div>
          <label for="gender-female">Female</label> 
          <input class="ml-2" type="radio" id="gender-female" name="gender-female" value="gender2" required>
        </div>
      </div> 
    </fieldset>

    <!-- Address Section-->
    <fieldset id="address" class="mt-2 pb-4">
      <p class="mt-1 mb-1 text-center ul">Address</p>
      <div class="grid-form">
        <div class="mb-1 mt-2">
          <label class="sm-w-200px" for="street">Street</label>
          <input class="float-right sm-float-none"  type="text" name="street" id="street" placeholder="100 Elizabeth Street" minlength="8" maxlength="40" required="required">
        </div>

        <div class="mb-1 mt-2">
          <label class="sm-w-200px" for="street">City</label>
          <input class="float-right sm-float-none"  type="text" name="city" id="city" placeholder="Melbourne" minlength="8" maxlength="40" required="required">
        </div>
        <div class="mt-2">
          <label class="sm-w-200px" for="city">State</label>
          <select class="float-right sm-float-none" name="state" id="state" required="required">
          <option value="">Please Select</option>
          <option value="vic">VIC</option>
          <option value="nsw">NSW</option>
          <option value="qld">QLD</option>
          <option value="nt">NT</option>
          <option value="wa">WA</option>
          <option value="sa">SA</option>
          <option value="tas">TAS</option>
          <option value="act">ACT</option>
          </select>
        </div>
        <div class="mt-2">
          <label class="sm-w-200px" for="postcode">Post Code</label>
          <input class="float-right sm-float-none"  type="text" name="postcode" id="postcode" placeholder="3000" pattern="[0-9]{4}" required="required">
        </div>
      </div>
    </fieldset>

    <!-- Contact Details -->
    <fieldset class="mt-2 pb-4">
      <p class="mt-1 mb-1 text-center ul">
        Provide your contact details
      </p>
      <div class="grid-form">
        <div class="mb-1 mt-2">
          <label for="email">Email</label>
          <input class="mr-2" type="text" name="email" id="email" placeholder="example@domain.com" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$" required="required">
        </div>
        <div class="mt-2">
          <label for="phone">Phone</label> 
          <input class="mr-2" type="text" name="phone" id="phone" placeholder="(+61) XXX-XXX-XXX" pattern="/^\({0,1}((0|\+61)(2|4|3|7|8)){0,1}\){0,1}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{1}(\ |-){0,1}[0-9]{3}$/"  required="required">
        </div> 
      </div>
    </fieldset>

    <!-- Skills Section -->
    <fieldset class="mt-2 pb-4">
      <p class="mt-1 mb-1 text-center ul">
        What skills make you stand out?
      </p>
      <div>
        <div class="mb-1 mt-2">
          <p>Skills:</p>
          <div class="grid-form-skills mt-2">
            <label for="html">HTML</label> 
			      <input type="checkbox" id="html" name="skills" value="html" checked="checked">
            <label for="css">CSS</label> 
            <input type="checkbox" id="css" name="skills" value="css">
            <label for="javascript">JavaScript</label> 
            <input type="checkbox" id="javascript" name="skills" value="javascript">
            <label for="php">PHP</label> 
            <input type="checkbox" id="php" name="skills" value="php">
            <label for="mysql">MySQL</label> 
            <input type="checkbox" id="mysql" name="skills" value="mysql">
            <label for="other">Other</label> 
            <input type="checkbox" id="other" name="skills" value="other">                 
          </div>
        </div>
      </div>

      <div class="mb-1 mt-4">
        <p class="mb-1">Other: Please describe</p>
        <textarea name="other" class="other-description" rows="10"></textarea>
      </div>
    </fieldset>

    <div class="mt-2 submission">
      <input type="submit" value="Register">
      <input type="reset" value="Reset Form">
    </div>
  </form>

</main>
<?php include 'footer.inc'; ?>
</body>
</html>