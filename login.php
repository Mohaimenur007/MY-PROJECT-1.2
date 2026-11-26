<?php

// Log in with username and password.

require "includes/init.php";

if(me())
{
    redirect("dashboard.php");
}

// Only allow coming back to a page on this site, like "item_detail.php?id=3".
$next = is_post() ? post("next") : get("next");
if(!preg_match('/^[a-z_]+\.php(\?[A-Za-z0-9_=&%.\-]*)?$/', $next)) $next = "";

$username = "";
$error = "";

if(is_post())
{
    check_csrf();

    $username = post("username");
    $password = isset($_POST["password"]) && is_string($_POST["password"]) ? $_POST["password"] : "";
    $locked_until = isset($_SESSION["login_locked_until"]) ? $_SESSION["login_locked_until"] : 0;

    if($locked_until > time())
    {
        $error = "Too many attempts. Please wait " . ($locked_until - time()) . " seconds and try again.";
    }
    else if($username == "" || $password == "")
    {
        $error = "Enter your username and password.";
    }
    else
    {
        $user = User::findByUsername($username);

        if($user && password_verify($password, $user["Password"]))
        {
            if($user["IsBanned"])
            {
                $error = "This account has been suspended. Use the contact page if you think this is a mistake.";
            }
            else
            {
                login_user($user);
                flash("Welcome back, " . $user["Username"] . "!");
                redirect($next != "" ? $next : "dashboard.php");
            }
        }
        else
        {
            // Five wrong tries in a row lock the form for one minute.
            $_SESSION["login_attempts"] = (isset($_SESSION["login_attempts"]) ? $_SESSION["login_attempts"] : 0) + 1;

            if($_SESSION["login_attempts"] >= 5)
            {
                $_SESSION["login_locked_until"] = time() + 60;
                $_SESSION["login_attempts"] = 0;
            }

            $error = "Invalid Username or Password";
        }
    }
}

$page_title = "Login";
$page = "login";
require "includes/header.php";
?>

<div class="container-xl auth-container">
    <div class="row align-items-center gy-5 gx-lg-5">

        <div class="col-lg-6">
            <div class="auth-side">
                <p class="eyebrow">Welcome back</p>
                <h1>Sign in to Lostly</h1>
                <p>Check your matches, answer claims and see whether anyone has spotted your item.</p>
                <ul class="auth-points">
                    <li><?= ic("sparkle") ?><div><b>Automatic matching</b>Every lost report is compared with every found report.</div></li>
                    <li><?= ic("lock") ?><div><b>Private by default</b>Talk to the other person without sharing your number.</div></li>
                </ul>

                <?php if(config("demo_mode")) { ?>
                <div class="demo-box">
                    <b>Try a demo account</b>
                    <span>Members use the password <code>lostly123</code>, the admin uses <code>admin123</code>.</span>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" class="btn btn-ghost btn-sm js-demo-login" data-user="admin" data-pass="admin123">admin · admin</button>
                        <button type="button" class="btn btn-ghost btn-sm js-demo-login" data-user="rana" data-pass="lostly123">rana</button>
                        <button type="button" class="btn btn-ghost btn-sm js-demo-login" data-user="mitu" data-pass="lostly123">mitu</button>
                        <button type="button" class="btn btn-ghost btn-sm js-demo-login" data-user="Didar" data-pass="lostly123">Didar</button>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-5 offset-lg-1">
            <div class="auth-box">
                <h2>Log in</h2>
                <p class="text-body-secondary">Enter your username and password.</p>

                <form method="post" action="login.php" class="js-validate" id="login-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="next" value="<?= e($next) ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= e($username) ?>" data-required="Enter Username" autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" data-required="Enter Password" autocomplete="current-password">
                    </div>

                    <?php if($error != "") { ?>
                    <div class="alert alert-lost form-message" role="alert"><?= e($error) ?></div>
                    <?php } ?>

                    <div class="form-actions"><button type="submit" class="btn btn-ink btn-lg w-100">Log in</button></div>
                </form>

                <p class="switch-link">Don't have an account? <a href="registration.php">Create one</a></p>
            </div>
        </div>

    </div>
</div>

<?php require "includes/footer.php"; ?>
