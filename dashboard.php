<?php

// Your dashboard: stats, claims waiting for you, possible matches, your reports and claims.

require "includes/init.php";
require_login();

$my_items = Item::byUser(my_id());
$incoming_claims = Claim::incoming(my_id());
$my_claims = Claim::sentBy(my_id());

$stats = ["total" => count($my_items), "active" => 0, "resolved" => 0, "views" => 0];
$match_groups = [];

foreach($my_items as $item)
{
    $stats[$item["Status"] == "Active" ? "active" : "resolved"]++;
    $stats["views"] += (int)$item["Views"];

    if($item["Status"] == "Active")
    {
        $matches = Item::matches($item, 3);
        if(count($matches) > 0) $match_groups[] = ["item" => $item, "matches" => $matches];
    }
}

$page_title = "Dashboard";
$page = "dashboard";
require "includes/header.php";
?>

<div class="container-xl page">

    <?= page_hero("Your dashboard", "Welcome back, " . e(me()["Username"]), "Here's what's happening with your reports today.",
        '<div class="d-flex flex-wrap gap-3 mt-3">'
        . '<a href="report_item.php?type=lost" class="btn btn-lost">' . ic("plus") . 'I lost something</a>'
        . '<a href="report_item.php?type=found" class="btn btn-found">' . ic("plus") . 'I found something</a>'
        . '<a href="browse.php" class="btn btn-ghost">' . ic("search") . 'Browse reports</a></div>', true) ?>

    <?= stat_tiles([
        "reports posted" => $stats["total"],
        "still active" => $stats["active"],
        "resolved" => $stats["resolved"],
        "views on your reports" => $stats["views"]
    ], "row-cols-2 row-cols-lg-4", ["clipboard", "clock", "checkCircle", "eye"]) ?>

    <?php if(count($incoming_claims) > 0) { ?>
    <section class="panel panel-alert">
        <div class="section-header"><h2>Claims waiting for you <?= badge("count", count($incoming_claims)) ?></h2></div>
        <div class="list-rows">
            <?php foreach($incoming_claims as $claim) { ?>
            <div class="list-row">
                <div class="list-row-info">
                    <strong><?= e($claim["Username"]) ?></strong>
                    <span><?= $claim["Type"] == "Found" ? "says this is theirs" : "says they found it" ?> · <a href="item_detail.php?id=<?= (int)$claim["ItemID"] ?>"><?= e($claim["ItemName"]) ?></a></span>
                    <span class="quote">“<?= e($claim["Message"]) ?>”</span>
                </div>
                <div class="list-row-actions"><a href="item_detail.php?id=<?= (int)$claim["ItemID"] ?>#claims" class="btn btn-ink btn-sm">Review</a></div>
            </div>
            <?php } ?>
        </div>
    </section>
    <?php } ?>

    <?php if(count($match_groups) > 0) { ?>
    <section class="panel">
        <div class="section-header">
            <h2><?= ic("sparkle", "h-ic") ?>Possible matches</h2>
            <span class="text-body-secondary">Reports that look like yours</span>
        </div>
        <?php foreach($match_groups as $group) { ?>
        <p class="match-for">For your <?= strtolower($group["item"]["Type"]) ?> report <a href="item_detail.php?id=<?= (int)$group["item"]["ID"] ?>"><?= e($group["item"]["ItemName"]) ?></a></p>
        <div class="item-grid">
            <?php foreach($group["matches"] as $match) { echo item_card($match, $match["Score"]); } ?>
        </div>
        <?php } ?>
    </section>
    <?php } ?>

    <section class="panel">
        <div class="section-header">
            <h2>My reports</h2>
            <a href="report_item.php" class="btn btn-ink btn-sm"><?= ic("plus") ?>New report</a>
        </div>

        <?php if(count($my_items) == 0) { ?>
        <?= empty_box("clipboard", "", "You haven't pinned any notices yet.") ?>
        <?php } else { ?>
        <div class="list-rows">
            <?php foreach($my_items as $item) { ?>
            <div class="list-row">
                <div class="list-row-info">
                    <span><?= badge(strtolower($item["Type"]), e($item["Type"])) ?> <?= $item["Status"] == "Resolved" ? badge("resolved", "Resolved") : "" ?></span>
                    <strong><?= e($item["ItemName"]) ?></strong>
                    <span><?= e($item["Category"]) ?> · <?= nice_date($item["EventDate"]) ?> · <?= (int)$item["Views"] ?> views</span>
                </div>
                <div class="list-row-actions">
                    <a href="item_detail.php?id=<?= (int)$item["ID"] ?>" class="btn btn-ghost btn-sm">View</a>
                    <a href="report_item.php?id=<?= (int)$item["ID"] ?>" class="btn btn-ghost btn-sm">Edit</a>
                    <form method="post" action="delete_item.php" class="d-inline" data-confirm="Delete this report? This cannot be undone.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$item["ID"] ?>">
                        <button type="submit" class="btn btn-danger-ghost btn-sm">Delete</button>
                    </form>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </section>

    <div class="row g-4">
        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="section-header"><h2>Claims I sent</h2></div>
                <?php if(count($my_claims) == 0) { ?>
                <p class="text-body-secondary">Nothing yet. Open a notice and press “Claim” if it's yours or you found it.</p>
                <?php } else { ?>
                <div class="list-rows">
                    <?php foreach($my_claims as $claim) { ?>
                    <div class="list-row">
                        <div class="list-row-info">
                            <strong><a href="item_detail.php?id=<?= (int)$claim["ItemID"] ?>"><?= e($claim["ItemName"]) ?></a></strong>
                            <span><?= time_ago($claim["CreatedAt"]) ?></span>
                        </div>
                        <?= badge(strtolower($claim["Status"]), e($claim["Status"])) ?>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="panel h-100">
                <div class="section-header">
                    <h2>Your information</h2>
                    <a href="edit_profile.php" class="btn btn-ghost btn-sm">Edit</a>
                </div>
                <div class="row row-cols-1 row-cols-sm-2 g-3">
                    <div class="col"><div class="info-item"><span>Username</span><strong><?= e(me()["Username"]) ?></strong></div></div>
                    <div class="col"><div class="info-item"><span>Email</span><strong><?= e(me()["Email"]) ?></strong></div></div>
                    <div class="col"><div class="info-item"><span>Phone</span><strong><?= e(me()["Phone"]) ?></strong></div></div>
                    <div class="col"><div class="info-item"><span>Location</span><strong><?= e(me()["District"]) ?>, <?= e(me()["Division"]) ?></strong></div></div>
                </div>
            </section>
        </div>
    </div>

</div>

<?php require "includes/footer.php"; ?>
