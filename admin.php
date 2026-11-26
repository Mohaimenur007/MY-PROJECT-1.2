<?php

// Admin panel: overview charts, reports, members, flags, inbox, activity log and settings.

require "includes/init.php";
require_admin();

$tabs = ["overview" => "Overview", "items" => "Reports", "users" => "Members", "flags" => "Flags",
         "inbox" => "Inbox", "log" => "Activity log", "settings" => "Settings"];

// ---- actions (all POST) ----
if(is_post())
{
    check_csrf();

    $action = post("action");
    $id = post_id();
    $tab = isset($tabs[post("tab")]) ? post("tab") : "overview";

    if($action == "delete_item")
    {
        $item = Item::find($id);

        if($item)
        {
            Item::delete($item);
            if($item["UserID"] != my_id()) Notification::send($item["UserID"], "admin", "An administrator removed your report “" . $item["ItemName"] . "”.", "dashboard.php");
            AdminLog::add(my_id(), "Removed report", "“" . $item["ItemName"] . "” by " . $item["Username"]);
            flash("Report removed.");
        }
    }
    else if($action == "dismiss_flag")
    {
        $flag = Flag::find($id);

        if($flag)
        {
            Flag::delete($id);
            AdminLog::add(my_id(), "Dismissed flag", "Flag on “" . $flag["ItemName"] . "”");
            flash("Flag dismissed.");
        }
    }
    else if(in_array($action, ["ban", "unban", "promote", "demote"], true))
    {
        $user = User::find($id);

        if(!$user)
        {
            flash("That member doesn't exist.", "error");
        }
        else if($id == my_id())
        {
            flash("You can't change your own account here.", "error");
        }
        else if($action == "ban" && $user["Role"] == "admin")
        {
            flash("Remove admin rights before suspending.", "error");
        }
        else
        {
            if($action == "ban") { User::setBanned($id, true); $log = "Suspended member"; $msg = " has been suspended."; }
            if($action == "unban") { User::setBanned($id, false); $log = "Unsuspended member"; $msg = " can log in again."; }
            if($action == "promote") { User::setRole($id, "admin"); $log = "Made admin"; $msg = " is now an admin."; }
            if($action == "demote") { User::setRole($id, "user"); $log = "Removed admin"; $msg = " is no longer an admin."; }

            AdminLog::add(my_id(), $log, $user["Username"]);
            flash($user["Username"] . $msg);
        }
    }
    else if($action == "delete_contact")
    {
        ContactMessage::delete($id);
        flash("Message deleted.");
    }
    else if($action == "announcement")
    {
        $text = shorten(post("announcement"), 300);
        Setting::set("announcement", $text);
        AdminLog::add(my_id(), "Updated announcement", $text != "" ? $text : "(removed)");
        flash($text != "" ? "Announcement saved." : "Announcement removed.");
    }

    redirect("admin.php", ["tab" => $tab == "overview" ? "" : $tab]);
}

// ---- CSV download of every report ----
if(get("export") == "csv")
{
    $cell = function ($v) {
        $v = (string)$v;
        if(preg_match('/^[=+\-@]/', $v)) $v = "'" . $v;   // stops spreadsheet formulas
        return '"' . str_replace('"', '""', $v) . '"';
    };

    header("Content-Type: text/csv; charset=utf-8");
    header('Content-Disposition: attachment; filename="reports.csv"');

    echo "\xEF\xBB\xBF";
    echo implode(",", array_map($cell, ["Reference", "Type", "Item", "Category", "Division", "District", "Date", "Status", "Reward", "Views", "Reporter", "Posted"])) . "\r\n";

    foreach(Item::all() as $r)
    {
        echo implode(",", array_map($cell, [ref_code($r["ID"]), $r["Type"], $r["ItemName"], $r["Category"], $r["Division"], $r["District"],
            $r["EventDate"], $r["Status"], $r["Reward"], $r["Views"], $r["Username"], substr($r["CreatedAt"], 0, 10)])) . "\r\n";
    }

    AdminLog::add(my_id(), "Exported reports", "reports.csv");
    exit();
}

$tab = isset($tabs[get("tab")]) ? get("tab") : "overview";
$open_flags = Flag::count();
$unread_inbox = ContactMessage::unreadCount();

// Draws horizontal bars from rows of Label / Total.
function bar_list($rows)
{
    if(count($rows) == 0) return '<p class="text-body-secondary">No data yet.</p>';

    $max = 1;
    foreach($rows as $row) $max = max($max, (int)$row["Total"]);

    $html = "";
    foreach($rows as $row)
    {
        $html .= '<div class="bar-row"><span class="bar-label">' . e($row["Label"]) . '</span>'
            . '<div class="progress flex-grow-1" role="progressbar" aria-label="' . e($row["Label"]) . '" aria-valuenow="' . (int)$row["Total"] . '" aria-valuemin="0" aria-valuemax="' . $max . '">'
            . '<div class="progress-bar" style="width: ' . round($row["Total"] * 100 / $max) . '%"></div></div>'
            . '<b>' . (int)$row["Total"] . '</b></div>';
    }

    return $html;
}

// A small form with one button, used for every admin action.
function admin_button($action, $id, $label, $class, $tab, $confirm = "")
{
    return '<form method="post" action="admin.php" class="d-inline"' . ($confirm != "" ? ' data-confirm="' . e($confirm) . '"' : '') . '>'
        . csrf_field()
        . '<input type="hidden" name="action" value="' . $action . '">'
        . '<input type="hidden" name="id" value="' . (int)$id . '">'
        . '<input type="hidden" name="tab" value="' . $tab . '">'
        . '<button type="submit" class="btn ' . $class . ' btn-sm">' . $label . '</button></form>';
}

$page_title = "Admin";
$page = "admin";
require "includes/header.php";
?>

<div class="container-xl page">

    <?= page_hero("Control room", "Admin panel", "Keep the board clean: review flagged posts, remove spam and manage members.", "", true) ?>

    <ul class="nav nav-pills admin-tabs">
        <?php foreach($tabs as $key => $label) { ?>
        <li class="nav-item">
            <a class="nav-link<?= $tab == $key ? " active" : "" ?>" href="admin.php<?= $key == "overview" ? "" : "?tab=" . $key ?>"<?= $tab == $key ? ' aria-current="page"' : "" ?>>
                <?= $label ?><?php if($key == "flags" && $open_flags > 0) echo " " . badge("count", $open_flags); ?><?php if($key == "inbox" && $unread_inbox > 0) echo " " . badge("count", $unread_inbox); ?>
            </a>
        </li>
        <?php } ?>
    </ul>

<?php if($tab == "overview") {

    $total = Item::count();
    $resolved = Item::count("resolved");
    $lost = Item::count("lost");
    $found = $total - $lost;
    $days = Item::lastSevenDays();
    $days_max = max(1, max($days));
    $rate = $total > 0 ? round($resolved * 100 / $total) : 0;
    $all = max(1, $total);
?>

    <?= stat_tiles([
        "Reports" => $total, "Active" => $total - $resolved, "Resolved" => $resolved,
        "Members" => User::count(), "Claims" => Claim::count(), "Messages" => Message::count()
    ], "row-cols-2 row-cols-md-3 row-cols-xl-6", ["clipboard", "clock", "checkCircle", "users", "usercheck", "chat"]) ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <section class="panel h-100">
                <h2>Reports in the last 7 days</h2>
                <div class="day-chart">
                    <?php foreach($days as $day => $n) { ?>
                    <div class="day-col" title="<?= plural($n, "report") ?> on <?= nice_date($day, "M j") ?>">
                        <b><?= $n ?></b>
                        <span class="day-bar"><i style="height: <?= round($n * 100 / $days_max) ?>%"></i></span>
                        <small><?= nice_date($day, "D") ?></small>
                    </div>
                    <?php } ?>
                </div>
            </section>
        </div>

        <div class="col-lg-6">
            <section class="panel h-100">
                <h2>Lost vs found</h2>
                <div class="progress-stacked split-bar" title="<?= $lost ?> lost, <?= $found ?> found">
                    <div class="progress" role="progressbar" aria-label="Lost" aria-valuenow="<?= $lost ?>" aria-valuemin="0" aria-valuemax="<?= $all ?>" style="width: <?= $lost * 100 / $all ?>%"><div class="progress-bar bar-lost"></div></div>
                    <div class="progress" role="progressbar" aria-label="Found" aria-valuenow="<?= $found ?>" aria-valuemin="0" aria-valuemax="<?= $all ?>" style="width: <?= $found * 100 / $all ?>%"><div class="progress-bar bar-found"></div></div>
                </div>
                <p class="d-flex gap-4 mt-2">
                    <span><i class="dot dot-lost"></i> Lost <b><?= $lost ?></b></span>
                    <span><i class="dot dot-found"></i> Found <b><?= $found ?></b></span>
                </p>
                <h2 class="sub-heading">Resolution rate</h2>
                <div class="bar-row">
                    <div class="progress flex-grow-1 tall" role="progressbar" aria-label="Resolution rate" aria-valuenow="<?= $rate ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bar-found" style="width: <?= $rate ?>%"></div></div>
                    <b><?= $rate ?>%</b>
                </div>
                <p class="text-body-secondary small mb-0"><?= $resolved ?> of <?= $total ?> reports ended with the item back home.</p>
            </section>
        </div>

        <div class="col-lg-6"><section class="panel h-100"><h2>By category</h2><?= bar_list(Item::countBy("Category")) ?></section></div>
        <div class="col-lg-6"><section class="panel h-100"><h2>By division</h2><?= bar_list(Item::countBy("Division")) ?></section></div>
    </div>

<?php } else if($tab == "items") { $q = shorten(get("q"), 100); $rows = $q != "" ? Item::adminSearch($q) : array_reverse(array_slice(Item::all(), -100)); ?>

    <form method="get" action="admin.php" class="panel filter-bar">
        <input type="hidden" name="tab" value="items">
        <div class="input-group">
            <input type="search" class="form-control" name="q" placeholder="Search by item name or username…" value="<?= e($q) ?>" aria-label="Search reports">
            <button type="submit" class="btn btn-ink">Search</button>
        </div>
    </form>

    <section class="panel">
        <?php if(count($rows) == 0) { ?><p class="text-body-secondary mb-0">No reports found.</p><?php } ?>
        <div class="list-rows">
            <?php foreach($rows as $r) { ?>
            <div class="list-row">
                <div class="list-row-info">
                    <span><?= badge(strtolower($r["Type"]), e($r["Type"])) ?> <?= $r["Status"] == "Resolved" ? badge("resolved", "Resolved") : "" ?></span>
                    <strong><?= e($r["ItemName"]) ?></strong>
                    <span>by <?= e($r["Username"]) ?> · <?= e($r["Category"]) ?> · <?= e($r["District"]) ?>, <?= e($r["Division"]) ?> · <?= time_ago($r["CreatedAt"]) ?></span>
                </div>
                <div class="list-row-actions">
                    <a href="item_detail.php?id=<?= (int)$r["ID"] ?>" class="btn btn-ghost btn-sm">View</a>
                    <?= admin_button("delete_item", $r["ID"], "Remove", "btn-danger-ghost", "items", "Remove this report for everyone? This cannot be undone.") ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

<?php } else if($tab == "users") { ?>

    <section class="panel">
        <div class="list-rows">
            <?php foreach(User::all() as $u) { $is_admin_row = $u["Role"] == "admin"; ?>
            <div class="list-row">
                <span class="avatar"><?= e(initial($u["Username"])) ?></span>
                <div class="list-row-info">
                    <strong>
                        <a href="member.php?id=<?= (int)$u["ID"] ?>"><?= e($u["Username"]) ?></a>
                        <?php if($is_admin_row) echo badge("approved", "Admin"); ?>
                        <?php if($u["IsBanned"]) echo badge("rejected", "Suspended"); ?>
                    </strong>
                    <span><?= e($u["Email"]) ?> · <?= e($u["Phone"]) ?></span>
                    <span><?= e($u["District"]) ?>, <?= e($u["Division"]) ?> · <?= plural((int)$u["ReportCount"], "report") ?></span>
                </div>
                <div class="list-row-actions">
                    <?php if($u["ID"] == my_id()) { ?>
                    <span class="text-body-secondary small">This is you</span>
                    <?php } else { ?>
                    <?= $is_admin_row
                        ? admin_button("demote", $u["ID"], "Remove admin", "btn-ghost", "users", "Remove admin rights from this member?")
                        : admin_button("promote", $u["ID"], "Make admin", "btn-ghost", "users", "Give this member full admin rights?") ?>
                    <?php if(!$is_admin_row) { ?>
                    <?= $u["IsBanned"]
                        ? admin_button("unban", $u["ID"], "Unsuspend", "btn-found", "users", "Let this member log in again?")
                        : admin_button("ban", $u["ID"], "Suspend", "btn-danger-ghost", "users", "Suspend this member? They will be logged out and their reports hidden.") ?>
                    <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

<?php } else if($tab == "flags") { $rows = Flag::open(); ?>

    <section class="panel">
        <?php if(count($rows) == 0) { ?><?= empty_box("shield", "", "No open flags. The board is clean.") ?><?php } ?>
        <div class="list-rows">
            <?php foreach($rows as $f) { ?>
            <div class="list-row">
                <div class="list-row-info">
                    <strong><?= e($f["ItemName"]) ?> <span class="text-body-secondary fw-normal">by <?= e($f["OwnerName"]) ?></span></strong>
                    <span class="quote">“<?= e($f["Reason"]) ?>”</span>
                    <span>flagged by <?= e($f["ReporterName"]) ?> · <?= time_ago($f["CreatedAt"]) ?></span>
                </div>
                <div class="list-row-actions">
                    <a href="item_detail.php?id=<?= (int)$f["ItemID"] ?>" class="btn btn-ghost btn-sm">View</a>
                    <?= admin_button("dismiss_flag", $f["FlagID"], "Dismiss", "btn-ghost", "flags") ?>
                    <?= admin_button("delete_item", $f["ItemID"], "Remove post", "btn-danger-ghost", "flags", "Remove this report for everyone? This cannot be undone.") ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

<?php } else if($tab == "inbox") { $rows = ContactMessage::all(); ContactMessage::markAllRead(); ?>

    <section class="panel">
        <?php if(count($rows) == 0) { ?><?= empty_box("inbox", "", "No messages from the Contact page yet.") ?><?php } ?>
        <div class="list-rows">
            <?php foreach($rows as $r) { ?>
            <div class="list-row align-items-start<?= $r["IsRead"] ? "" : " unread" ?>">
                <span class="avatar"><?= e(initial($r["Name"])) ?></span>
                <div class="list-row-info">
                    <strong><?= e($r["Subject"]) ?> <?php if(!$r["IsRead"]) echo badge("count", "new"); ?></strong>
                    <span><?= e($r["Name"]) ?> · <a href="mailto:<?= e($r["Email"]) ?>"><?= e($r["Email"]) ?></a> · <?= time_ago($r["CreatedAt"]) ?></span>
                    <span class="tip-body"><?= nl2br(e($r["Body"])) ?></span>
                </div>
                <div class="list-row-actions">
                    <?= admin_button("delete_contact", $r["ID"], "Delete", "btn-danger-ghost", "inbox", "Delete this message?") ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

<?php } else if($tab == "log") { $rows = AdminLog::recent(); ?>

    <section class="panel">
        <?php if(count($rows) == 0) { ?>
        <p class="text-body-secondary mb-0">Nothing has been done in the admin panel yet.</p>
        <?php } else { ?>
        <div class="table-responsive">
            <table class="table log-table align-middle mb-0">
                <thead><tr><th scope="col">When</th><th scope="col">Admin</th><th scope="col">Action</th><th scope="col">Detail</th></tr></thead>
                <tbody>
                    <?php foreach($rows as $r) { ?>
                    <tr>
                        <td class="text-nowrap"><?= nice_date($r["CreatedAt"], "M j, g:i a") ?></td>
                        <td><?= e($r["Username"] ? $r["Username"] : "—") ?></td>
                        <td><b><?= e($r["Action"]) ?></b></td>
                        <td class="text-break"><?= e($r["Detail"]) ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>

<?php } else { ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" action="admin.php" class="panel h-100">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="announcement">
                <input type="hidden" name="tab" value="settings">
                <h2>Announcement banner</h2>
                <p class="text-body-secondary">Shown at the top of every page to everyone. Leave it empty to remove the banner.</p>
                <label for="announcement" class="form-label">Message</label>
                <textarea class="form-control" id="announcement" name="announcement" rows="3" maxlength="300" placeholder="e.g. Lostly will be offline on Friday night from 11pm to 1am for maintenance."><?= e(Setting::get("announcement")) ?></textarea>
                <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-ink">Save</button></div>
            </form>
        </div>
        <div class="col-lg-5">
            <section class="panel h-100">
                <h2>Export data</h2>
                <p class="text-body-secondary">Download every report as a CSV file that opens in Excel or Google Sheets.</p>
                <a href="admin.php?export=csv" class="btn btn-ghost"><?= ic("download") ?>Download reports.csv</a>
            </section>
        </div>
    </div>

<?php } ?>

</div>

<?php require "includes/footer.php"; ?>
