<?php

// Loaded at the top of every page: settings, database, helpers and the logged-in user.

define("ROOT", dirname(__DIR__));

date_default_timezone_set("Asia/Dhaka");

// Safer session cookie: not readable by JavaScript, not sent from other sites.
ini_set("session.use_strict_mode", "1");
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off"
]);
session_start();

// Basic browser protections.
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: same-origin");

require ROOT . "/db.php";
require ROOT . "/lists.php";
require __DIR__ . "/helpers.php";
require __DIR__ . "/icons.php";
require __DIR__ . "/models.php";
require ROOT . "/ItemModel.php";
require __DIR__ . "/components.php";

// ---- who is logged in ----

$me = null;

if(isset($_SESSION["id"]))
{
    $me = User::find((int)$_SESSION["id"]);

    // Account removed or suspended while logged in: end the session.
    if(!$me || $me["IsBanned"])
    {
        $_SESSION = [];
        $me = null;
    }
}

function me()
{
    global $me;
    return $me;
}

function my_id()
{
    global $me;
    return $me ? (int)$me["ID"] : 0;
}

function is_admin()
{
    global $me;
    return $me && $me["Role"] == "admin";
}

function login_user($user)
{
    // A new session ID on login stops session fixation.
    session_regenerate_id(true);
    unset($_SESSION["login_attempts"], $_SESSION["login_locked_until"]);
    $_SESSION["id"] = (int)$user["ID"];
}

// The current page and its query string, used to come back after logging in.
function current_page()
{
    $page = basename($_SERVER["SCRIPT_NAME"]);
    return $page . (!empty($_SERVER["QUERY_STRING"]) ? "?" . $_SERVER["QUERY_STRING"] : "");
}

function require_login()
{
    if(!me())
    {
        flash("Please log in first.", "error");
        redirect("login.php", ["next" => is_post() ? "" : current_page()]);
    }
}

function require_admin()
{
    require_login();

    if(!is_admin())
    {
        flash("That page is for administrators.", "error");
        redirect("dashboard.php");
    }
}
