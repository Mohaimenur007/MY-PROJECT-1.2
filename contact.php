<?php

// Contact form. Messages go to the admin inbox.

require "includes/init.php";

$old = [
    "name" => me() ? me()["Username"] : "",
    "email" => me() ? me()["Email"] : "",
    "subject" => "",
    "message" => ""
];
$error = "";

if(is_post())
{
    check_csrf();

    foreach($old as $key => $value)
    {
        $old[$key] = post($key);
    }

    // A hidden field people never see. Bots that fill it in are ignored.
    if(post("website") != "")
    {
        redirect("index.php");
    }

    if($old["name"] == "" || text_length($old["name"]) > 100) $error = "Enter your name.";
    else if(!filter_var($old["email"], FILTER_VALIDATE_EMAIL)) $error = "Enter a valid email.";
    else if($old["subject"] == "" || text_length($old["subject"]) > 150) $error = "Enter a subject (up to 150 characters).";
    else if(text_length($old["message"]) < 10 || text_length($old["message"]) > 3000) $error = "Write a message of at least 10 characters.";

    if($error == "")
    {
        ContactMessage::create($old["name"], $old["email"], $old["subject"], $old["message"]);
        flash("Thanks! Your message was sent to the Lostly team.");
        redirect("index.php");
    }
}

$page_title = "Contact";
require "includes/header.php";
?>

<div class="container-xl page page-narrow">

    <?= page_hero("Contact", "Send us a message", "Questions, problems with a report, or a request to close your account. An administrator reads every message.") ?>

    <form method="post" action="contact.php" class="panel js-validate">
        <?= csrf_field() ?>

        <div class="visually-hidden" aria-hidden="true">
            <label for="website">Leave this empty</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Your name</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="<?= e($old["name"]) ?>" data-required="Enter your name">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="150" value="<?= e($old["email"]) ?>" data-required="Enter a valid email">
            </div>
            <div class="col-12">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" maxlength="150" value="<?= e($old["subject"]) ?>" data-required="Enter a subject (up to 150 characters)">
            </div>
            <div class="col-12">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="6" maxlength="3000" data-required="Write a message of at least 10 characters" data-minlength="10" data-minlength-message="Write a message of at least 10 characters"><?= e($old["message"]) ?></textarea>
            </div>
        </div>

        <?php if($error != "") { ?>
        <div class="alert alert-lost form-message mt-3" role="alert"><?= e($error) ?></div>
        <?php } ?>

        <div class="d-flex justify-content-end mt-4 form-actions">
            <button type="submit" class="btn btn-ink">Send message</button>
        </div>
    </form>

</div>

<?php require "includes/footer.php"; ?>
