<?php

// Create a new account.

require "includes/init.php";

if(me())
{
    redirect("dashboard.php");
}

$old = ["username" => "", "email" => "", "phone" => "", "division" => "", "district" => ""];
$error = "";

if(is_post())
{
    check_csrf();

    foreach($old as $key => $value)
    {
        $old[$key] = post($key);
    }

    $password = isset($_POST["password"]) && is_string($_POST["password"]) ? $_POST["password"] : "";
    $confirm = isset($_POST["confirm_password"]) && is_string($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";

    if(!preg_match('/^[\p{L}\p{N}_.\- ]{3,30}$/u', $old["username"])) $error = "Username must be 3–30 letters, numbers, spaces, dots or dashes.";
    else if(User::usernameTaken($old["username"])) $error = "That username already exists. Try another one.";
    else if(!filter_var($old["email"], FILTER_VALIDATE_EMAIL) || strlen($old["email"]) > 150) $error = "Enter a valid email address.";
    else if(User::emailTaken($old["email"])) $error = "An account with that email already exists.";
    else if(!preg_match('/^[0-9+\-\s]{6,20}$/', $old["phone"])) $error = "Enter a valid phone number.";
    else if(!in_array($old["division"], $divisions, true)) $error = "Select your division.";
    else if($old["district"] == "" || text_length($old["district"]) > 100) $error = "Enter your district.";
    else if(strlen($password) < 6) $error = "Password must be at least 6 characters.";
    else if($password !== $confirm) $error = "Passwords do not match.";

    if($error == "")
    {
        $new_id = User::create($old + ["password" => $password]);
        login_user(User::find($new_id));
        flash("Account created. Welcome to Lostly, " . $old["username"] . "!");
        redirect("dashboard.php");
    }
}

$page_title = "Create account";
$page = "register";
require "includes/header.php";
?>

<div class="container-xl auth-container">
    <div class="row align-items-center gy-5 gx-lg-5">

        <div class="col-lg-5">
            <div class="auth-side">
                <p class="eyebrow">Create your account</p>
                <h1>Join the lost &amp; found network</h1>
                <p>Post reports, get automatic matches and message other members without sharing your number first.</p>
                <ul class="auth-points">
                    <li><?= ic("clipboard") ?><div><b>Post in about a minute</b>Photos, location, date and an optional reward.</div></li>
                    <li><?= ic("bell") ?><div><b>Alerts that matter</b>Hear about matches, claims and saved searches.</div></li>
                    <li><?= ic("shield") ?><div><b>Safe handovers</b>Claims need proof, and admins moderate the board.</div></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="auth-box">
                <h2>Create account</h2>
                <p class="text-body-secondary">It's free and takes about a minute.</p>

                <form method="post" action="registration.php" class="js-validate">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" maxlength="30" value="<?= e($old["username"]) ?>" data-required="Enter Username" autocomplete="username">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= e($old["email"]) ?>" data-required="Enter Email" autocomplete="email">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" maxlength="20" value="<?= e($old["phone"]) ?>" data-required="Enter Phone Number" autocomplete="tel">
                        </div>
                        <div class="col-md-6">
                            <label for="division" class="form-label">Division</label>
                            <select class="form-select" id="division" name="division" data-required="Select Division"><?= options($divisions, $old["division"], "Select Division") ?></select>
                        </div>
                        <div class="col-12">
                            <label for="district" class="form-label">District</label>
                            <input type="text" class="form-control" id="district" name="district" maxlength="100" list="district-list" value="<?= e($old["district"]) ?>" data-required="Enter District" autocomplete="off">
                            <?= district_datalist() ?>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" data-required="Enter Password" data-minlength="6" data-minlength-message="Password must be at least 6 characters" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" data-match="password" data-match-message="Passwords do not match" autocomplete="new-password">
                        </div>
                    </div>

                    <?php if($error != "") { ?>
                    <div class="alert alert-lost form-message mt-3" role="alert"><?= e($error) ?></div>
                    <?php } ?>

                    <div class="form-actions"><button type="submit" class="btn btn-ink btn-lg w-100 mt-3">Create account</button></div>
                </form>

                <p class="switch-link">Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </div>

    </div>
</div>

<?php require "includes/footer.php"; ?>
