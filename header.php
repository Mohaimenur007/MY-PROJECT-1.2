<?php

// Top of every page. Set $page_title and $page (which menu link is active) before including.

$page_title = isset($page_title) ? $page_title : "Lostly";
$page = isset($page) ? $page : "";
$body_class = isset($body_class) ? $body_class : "";
$announcement = Setting::get("announcement");
$nav_active = function ($name) use ($page) { return $name == $page ? " active" : ""; };
$nav_count = function ($n) { return $n > 0 ? ' <span class="badge rounded-pill badge-count">' . (int)$n . '</span>' : ''; };

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> · Lostly</title>
<meta name="description" content="Lostly — the lost and found network for Bangladesh. Report what you lost or found, get matched automatically and message the other person safely.">
<meta name="theme-color" content="#3A2418">
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<script>
// Apply the saved light/dark mode and colour scheme before the page paints.
(function () {
    document.documentElement.classList.add("js");
    try {
        var saved = localStorage.getItem("lostly-theme");
        if (!saved && window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) { saved = "dark"; }
        if (saved === "dark" || saved === "light") { document.documentElement.setAttribute("data-bs-theme", saved); }
        var pal = localStorage.getItem("lostly-palette");
        if (pal === "stone" || pal === "teal" || pal === "gold") { document.documentElement.setAttribute("data-palette", pal); }
    } catch (err) {}
})();
</script>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/fonts.css">
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/components.css">
<link rel="stylesheet" href="css/extras.css">
</head>
<body data-page="<?= e($page) ?>" class="<?= e($body_class) ?>">

<nav class="navbar navbar-expand-lg sticky-top lostly-nav">
    <div class="container-xl">
        <a href="index.php" class="navbar-brand logo">
            <span class="logo-mark"><?= ic("pin") ?></span>Lostly
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <?php if(me()) { ?>
                <li class="nav-item"><a class="nav-link<?= $nav_active("dashboard") ?>" href="dashboard.php">Dashboard</a></li>
                <?php } ?>
                <li class="nav-item"><a class="nav-link<?= $nav_active("browse") ?>" href="browse.php">Browse</a></li>
                <li class="nav-item"><a class="nav-link<?= $nav_active("reunited") ?>" href="reunited.php">Reunited</a></li>

                <?php if(me()) { ?>
                <li class="nav-item"><a class="nav-link<?= $nav_active("messages") ?>" href="messages.php">Messages<?= $nav_count(Message::unreadCount(my_id())) ?></a></li>
                <li class="nav-item"><a class="nav-link<?= $nav_active("alerts") ?>" href="alerts.php">Alerts<?= $nav_count(Notification::unreadCount(my_id())) ?></a></li>
                <?php if(is_admin()) { ?>
                <li class="nav-item"><a class="nav-link<?= $nav_active("admin") ?>" href="admin.php">Admin<?= $nav_count(Flag::count() + ContactMessage::unreadCount()) ?></a></li>
                <?php } ?>
                <li class="nav-item"><a class="btn btn-warn btn-sm nav-cta" href="report_item.php"><?= ic("plus") ?>Report</a></li>
                <?php } else { ?>
                <li class="nav-item"><a class="nav-link<?= $nav_active("safety") ?>" href="page.php?p=safety">Safety</a></li>
                <li class="nav-item"><a class="nav-link<?= $nav_active("login") ?>" href="login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-warn btn-sm nav-cta" href="registration.php">Join free</a></li>
                <?php } ?>

                <li class="nav-item dropdown">
                    <button type="button" class="theme-toggle palette-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Choose colour scheme" title="Colour scheme"><?= ic("palette") ?></button>
                    <ul class="dropdown-menu dropdown-menu-end palette-menu">
                        <li><span class="dropdown-item-text small text-body-secondary">Colour scheme</span></li>
                        <?php foreach($palettes as $nav_palette) { ?>
                        <li>
                            <button type="button" class="dropdown-item js-palette" data-set-palette="<?= e($nav_palette["id"]) ?>">
                                <span class="swatches"><?php foreach($nav_palette["colors"] as $nav_color) { ?><i style="background:<?= e($nav_color) ?>"></i><?php } ?></span>
                                <span class="pal-text"><b><?= e($nav_palette["name"]) ?></b><small><?= e($nav_palette["desc"]) ?></small></span>
                                <?= ic("check", "pal-check") ?>
                            </button>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <button type="button" class="theme-toggle js-theme-toggle" aria-label="Switch light and dark theme" title="Switch theme"><?= ic("moon", "i-moon") ?><?= ic("sun", "i-sun") ?></button>
                </li>

                <?php if(me()) { ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
                        <span class="avatar avatar-sm"><?= e(initial(me()["Username"])) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-body-secondary">Signed in as <b><?= e(me()["Username"]) ?></b></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="member.php?id=<?= my_id() ?>"><?= ic("user") ?>My public profile</a></li>
                        <li><a class="dropdown-item" href="saved.php"><?= ic("bookmark") ?>Saved reports</a></li>
                        <li><a class="dropdown-item" href="edit_profile.php"><?= ic("sliders") ?>Account settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="logout.php">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item"><?= ic("logout") ?>Log out</button>
                            </form>
                        </li>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<?php if($announcement != "") { ?>
<div class="announcement" role="status"><div class="container-xl"><?= ic("megaphone") ?><?= e($announcement) ?></div></div>
<?php } ?>

<!-- One-time messages (shown as toasts by js/main.js) -->
<div class="toast-container position-fixed end-0 p-3 lostly-toasts" id="toasts" aria-live="polite">
    <?php foreach(take_flashes() as $nav_flash) { ?>
    <div class="toast lostly-toast toast-<?= e($nav_flash["type"]) ?>" role="alert">
        <div class="d-flex">
            <div class="toast-body"><?= e($nav_flash["message"]) ?></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php } ?>
</div>

<main>
