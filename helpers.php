<?php

// Small functions used by every page.

// Settings that are not database or list values.
function config($key)
{
    $settings = [
        "per_page" => 9,              // reports per page on Browse
        "match_threshold" => 55,      // minimum score (0-100) to count as a match
        "max_photos" => 4,            // photos per report
        "max_upload_bytes" => 5 * 1024 * 1024,
        "max_search_alerts" => 10,
        "demo_mode" => true           // shows the demo accounts on the login page; set false before going live
    ];

    return $settings[$key];
}

// Escapes text for HTML. Use it on everything a user typed.
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Link to a page with optional query values: url("browse.php", ["category" => "Keys"]).
// Empty values are left out.
function url($page, $query = [])
{
    $query = array_filter($query, function ($v) { return $v !== "" && $v !== null; });
    return $page . (count($query) > 0 ? "?" . http_build_query($query) : "");
}

function redirect($page, $query = [])
{
    header("Location: " . url($page, $query));
    exit();
}

// Sends the visitor back to where the form was, or to $fallback.
function back($fallback = "index.php")
{
    $target = isset($_POST["back"]) ? (string)$_POST["back"] : "";

    // Only allow links inside this site (no "http://", no "//").
    if($target === "" || preg_match('#^(/|[a-z]+:)#i', $target) || strpos($target, "\\") !== false)
    {
        $target = $fallback;
    }

    header("Location: " . $target);
    exit();
}

// One-time messages shown as toasts on the next page.
function flash($message, $type = "success")
{
    $_SESSION["flash"][] = ["type" => $type, "message" => $message];
}

function take_flashes()
{
    $list = isset($_SESSION["flash"]) ? $_SESSION["flash"] : [];
    unset($_SESSION["flash"]);
    return $list;
}

// ---- form values ----

function post($key)
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : "";
}

function get($key)
{
    return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : "";
}

function get_id($key = "id")
{
    $value = get($key);
    return preg_match('/^[1-9][0-9]{0,9}$/', $value) ? (int)$value : 0;
}

function post_id($key = "id")
{
    $value = post($key);
    return preg_match('/^[1-9][0-9]{0,9}$/', $value) ? (int)$value : 0;
}

function text_length($text)
{
    return function_exists("mb_strlen") ? mb_strlen($text, "UTF-8") : strlen($text);
}

// ---- CSRF protection: every form sends back a secret token ----

function csrf_token()
{
    if(empty($_SESSION["csrf"]))
    {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf"];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Call at the top of every POST handler.
function check_csrf()
{
    $sent = isset($_POST["csrf"]) && is_string($_POST["csrf"]) ? $_POST["csrf"] : "";

    if(empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $sent))
    {
        http_response_code(400);
        die("This form has expired. Please go back, refresh the page and try again.");
    }
}

function is_post()
{
    return $_SERVER["REQUEST_METHOD"] === "POST";
}

// ---- formatting ----

function time_ago($datetime)
{
    $diff = time() - strtotime($datetime);

    if($diff < 60) return "just now";
    if($diff < 3600) return floor($diff / 60) . " min ago";
    if($diff < 86400) return floor($diff / 3600) . " hr ago";
    if($diff < 86400 * 7) return floor($diff / 86400) . " day" . (floor($diff / 86400) == 1 ? "" : "s") . " ago";

    return date("M j, Y", strtotime($datetime));
}

function nice_date($date, $format = "M j, Y")
{
    return date($format, strtotime($date));
}

function taka($amount)
{
    return "৳" . number_format((int)$amount);
}

// Public reference such as LF-00042.
function ref_code($id)
{
    return "LF-" . str_pad((string)$id, 5, "0", STR_PAD_LEFT);
}

function initial($name)
{
    $first = function_exists("mb_substr") ? mb_substr((string)$name, 0, 1, "UTF-8") : substr((string)$name, 0, 1);
    return function_exists("mb_strtoupper") ? mb_strtoupper($first, "UTF-8") : strtoupper($first);
}

function shorten($text, $length)
{
    $text = (string)$text;

    if(text_length($text) <= $length)
    {
        return $text;
    }

    return (function_exists("mb_substr") ? mb_substr($text, 0, $length - 1, "UTF-8") : substr($text, 0, $length - 1)) . "…";
}

function plural($count, $word, $many = null)
{
    return $count . " " . ($count == 1 ? $word : ($many ? $many : $word . "s"));
}

function selected($a, $b)
{
    return $a === $b ? " selected" : "";
}

// Full web address of a page on this site, for share links and posters.
function absolute_url($page)
{
    $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off";
    $host = isset($_SERVER["HTTP_HOST"]) ? preg_replace('/[^A-Za-z0-9.\-:\[\]]/', "", $_SERVER["HTTP_HOST"]) : "localhost";
    $folder = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

    return ($https ? "https" : "http") . "://" . $host . $folder . "/" . $page;
}
