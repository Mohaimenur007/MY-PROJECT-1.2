<?php

// Account settings: contact details, privacy and password.

require "includes/init.php";
require_login();

$me = me();
$old = [
    "username" => $me["Username"], "email" => $me["Email"], "phone" => $me["Phone"],
    "division" => $me["Division"], "district" => $me["District"], "show_contact" => (int)$me["ShowContact"]
];
$error = "";

if(is_post())
{
    check_csrf();

    foreach(["username", "email", "phone", "division", "district"] as $key)
    {
        $old[$key] = post($key);
    }

    $old["show_contact"] = isset($_POST["show_contact"]) ? 1 : 0;

    $current = isset($_POST["current_password"]) && is_string($_POST["current_password"]) ? $_POST["current_password"] : "";
    $new = isset($_POST["new_password"]) && is_string($_POST["new_password"]) ? $_POST["new_password"] : "";
    $confirm = isset($_POST["confirm_password"]) && is_string($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";

    if(!preg_match('/^[\p{L}\p{N}_.\- ]{3,30}$/u', $old["username"])) $error = "Username must be 3–30 letters, numbers, spaces, dots or dashes.";
    else if(User::usernameTaken($old["username"], my_id())) $error = "That username is already taken.";
    else if(!filter_var($old["email"], FILTER_VALIDATE_EMAIL) || strlen($old["email"]) > 150) $error = "Enter a valid email address.";
    else if(User::emailTaken($old["email"], my_id())) $error = "Another account already uses that email.";
    else if(!preg_match('/^[0-9+\-\s]{6,20}$/', $old["phone"])) $error = "Enter a valid phone number.";
    else if(!in_array($old["division"], $divisions, true)) $error = "Select your division.";
    else if($old["district"] == "" || text_length($old["district"]) > 100) $error = "Enter your district.";
    else if($new != "" || $current != "")
    {
        if(!password_verify($current, $me["Password"])) $error = "Your current password is not correct.";
        else if(strlen($new) < 6) $error = "The new password must be at least 6 characters.";
        else if($new !== $confirm) $error = "New passwords do not match.";
    }

    if($error == "")
    {
        User::update(my_id(), $old);

        if($new != "")
        {
            User::setPassword(my_id(), $new);
        }

        flash("Your profile was updated successfully.");
        redirect("dashboard.php");
    }
}

$page_title = "Account settings";
require "includes/header.php";
?>

<div class="container-xl page page-narrow">

    <?= page_hero("Account settings", "Edit Profile", "Keep your contact and location details up to date so other users can reach you when needed.") ?>

    <form method="post" action="edit_profile.php" class="panel js-validate">
        <?= csrf_field() ?>

        <div class="profile-head">
            <span class="avatar avatar-lg"><?= e(initial($me["Username"])) ?></span>
            <div>
                <strong><?= e($me["Username"]) ?></strong>
                <span class="text-body-secondary"><?= is_admin() ? "Administrator" : "Member" ?> · joined <?= nice_date($me["CreatedAt"], "M Y") ?></span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" maxlength="30" value="<?= e($old["username"]) ?>" data-required="Enter Username">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= e($old["email"]) ?>" data-required="Enter Email">
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" maxlength="20" value="<?= e($old["phone"]) ?>" data-required="Enter Phone Number">
            </div>
            <div class="col-md-6">
                <label for="division" class="form-label">Division</label>
                <select class="form-select" id="division" name="division" data-required="Select Division"><?= options($divisions, $old["division"]) ?></select>
            </div>
            <div class="col-12">
                <label for="district" class="form-label">District</label>
                <input type="text" class="form-control" id="district" name="district" maxlength="100" list="district-list" value="<?= e($old["district"]) ?>" data-required="Enter District" autocomplete="off">
                <?= district_datalist() ?>
            </div>
        </div>

        <h2 class="sub-heading">Privacy</h2>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="show_contact" name="show_contact" value="1"<?= $old["show_contact"] ? " checked" : "" ?>>
            <label class="form-check-label plain-label" for="show_contact">
                Show my phone and email on my reports
                <small class="d-block text-body-secondary">Only logged-in members can see them. Switched off, people reach you through private messages.</small>
            </label>
        </div>

        <h2 class="sub-heading">Change password <span class="text-body-secondary fw-normal">(optional)</span></h2>
        <div class="row g-3">
            <div class="col-12">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Only needed to set a new password" autocomplete="current-password">
            </div>
            <div class="col-md-6">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current" autocomplete="new-password">
            </div>
            <div class="col-md-6">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat new password" data-match="new_password" data-match-message="New passwords do not match" autocomplete="new-password">
            </div>
        </div>

        <?php if($error != "") { ?>
        <div class="alert alert-lost form-message mt-3" role="alert"><?= e($error) ?></div>
        <?php } ?>

        <div class="d-flex flex-wrap justify-content-end gap-3 mt-4 form-actions">
            <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-ink">Save Changes</button>
        </div>
    </form>

</div>

<?php require "includes/footer.php"; ?>
