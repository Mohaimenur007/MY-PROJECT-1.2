<?php

// Alerts (matches, claims, tips, admin notes) and saved searches.

require "includes/init.php";
require_login();

if(is_post())
{
    check_csrf();

    if(post("action") == "clear")
    {
        Notification::clear(my_id());
        flash("Alerts cleared.");
    }
    else if(post("action") == "delete_search")
    {
        SearchAlert::delete(post_id(), my_id());
        flash("Saved search removed.");
    }

    redirect("alerts.php");
}

$list = Notification::forUser(my_id());
$searches = SearchAlert::forUser(my_id());

// Shown once as "new", then marked as read.
Notification::markAllRead(my_id());

$page_title = "Alerts";
$page = "alerts";
require "includes/header.php";
?>

<div class="container-xl page page-narrow">

    <?= page_hero("Alerts", "What you missed", "New matches, claims and decisions on your reports.") ?>

    <section class="panel">
        <?php if(count($list) == 0) { ?>

        <?= empty_box("bell", "", "You're all caught up.") ?>

        <?php } else { ?>

        <div class="section-header">
            <h2>Recent</h2>
            <form method="post" action="alerts.php" data-confirm="Clear all alerts?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-ghost btn-sm">Clear all</button>
            </form>
        </div>

        <div class="list-rows">
            <?php foreach($list as $n) { ?>
            <a class="list-row list-row-link<?= $n["IsRead"] ? "" : " unread" ?>" href="<?= e($n["Link"]) ?>">
                <span class="avatar avatar-icon is-<?= e($n["Type"]) ?>"><?= ic(isset(ALERT_ICONS[$n["Type"]]) ? ALERT_ICONS[$n["Type"]] : "bell") ?></span>
                <div class="list-row-info">
                    <strong><?= e($n["Body"]) ?></strong>
                    <span><?= time_ago($n["CreatedAt"]) ?></span>
                </div>
                <?php if(!$n["IsRead"]) echo badge("count", "new"); ?>
            </a>
            <?php } ?>
        </div>

        <?php } ?>
    </section>

    <section class="panel" id="searches">
        <div class="section-header">
            <h2>Saved searches</h2>
            <a href="browse.php" class="btn btn-ghost btn-sm"><?= ic("plus") ?>Add from Browse</a>
        </div>

        <?php if(count($searches) == 0) { ?>
        <p class="text-body-secondary mb-0">Set filters or a keyword on the Browse page, then press “Alert me about new reports like this”. You'll get an alert here whenever a matching report is posted.</p>
        <?php } else { ?>
        <div class="list-rows">
            <?php foreach($searches as $s) { ?>
            <div class="list-row">
                <span class="avatar avatar-icon"><?= ic("bell") ?></span>
                <div class="list-row-info">
                    <strong><?= e(SearchAlert::describe($s)) ?></strong>
                    <span>saved <?= time_ago($s["CreatedAt"]) ?></span>
                </div>
                <div class="list-row-actions">
                    <a class="btn btn-ghost btn-sm" href="<?= e(url("browse.php", ["search" => $s["Keyword"], "type" => $s["Type"], "category" => $s["Category"], "division" => $s["Division"]])) ?>">Run</a>
                    <form method="post" action="alerts.php" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_search">
                        <input type="hidden" name="id" value="<?= (int)$s["ID"] ?>">
                        <button type="submit" class="btn btn-danger-ghost btn-sm">Remove</button>
                    </form>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </section>

</div>

<?php require "includes/footer.php"; ?>
