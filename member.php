<?php

// A member's public profile: badges, numbers and active reports.

require "includes/init.php";

$member = User::find(get_id());

if(!$member || ($member["IsBanned"] && !is_admin()))
{
    http_response_code(404);
    $page_title = "Not found";
    require "includes/header.php";
    echo '<div class="container-xl page page-narrow">' . empty_box("compass", "Member not found", "That member doesn't exist.", '<a href="index.php" class="btn btn-ink mt-2">Go to the home page</a>') . '</div>';
    require "includes/footer.php";
    exit();
}

$mid = (int)$member["ID"];
$all = Item::byUser($mid);
$stats = [
    "reports" => count($all),
    "resolved" => 0,
    "tips" => Comment::countBy($mid),
    // Items this member helped return: approved claims on lost reports + their own resolved found reports.
    "returned" => (int)Database::value(
        "SELECT COUNT(*) FROM claims JOIN items ON claims.ItemID = items.ID WHERE claims.ClaimantID=? AND claims.Status='Approved' AND items.Type='Lost'", "i", [$mid])
];

foreach($all as $item)
{
    if($item["Status"] == "Resolved")
    {
        $stats["resolved"]++;
        if($item["Type"] == "Found") $stats["returned"]++;
    }
}

// Badges are earned from real activity.
$badges = [];
if($stats["returned"] >= 1) $badges[] = ["heart", "Returned " . plural($stats["returned"], "item")];
if($stats["resolved"] >= 1) $badges[] = ["checkCircle", plural($stats["resolved"], "report") . " resolved"];
if($stats["tips"] >= 3) $badges[] = ["bulb", "Helpful tipster"];
if($member["Role"] == "admin") $badges[] = ["shield", "Lostly admin"];

$active = Item::activeByUser($mid);

$page_title = $member["Username"];
require "includes/header.php";
?>

<div class="container-xl page">

    <section class="panel member-head">
        <span class="avatar avatar-lg"><?= e(initial($member["Username"])) ?></span>
        <div class="member-info">
            <h1><?= e($member["Username"]) ?> <?php if($member["IsBanned"]) echo badge("rejected", "Suspended"); ?></h1>
            <p class="text-body-secondary mb-2"><?= ic("pin") ?><?= e($member["District"]) ?>, <?= e($member["Division"]) ?> · member since <?= nice_date($member["CreatedAt"], "F Y") ?></p>
            <?php if(count($badges) > 0) { ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach($badges as $b) { ?><span class="member-badge"><?= ic($b[0]) ?><?= e($b[1]) ?></span><?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php if(my_id() == $mid) { ?>
        <a href="edit_profile.php" class="btn btn-ghost btn-sm">Account settings</a>
        <?php } ?>
    </section>

    <?= stat_tiles([
        "reports posted" => $stats["reports"],
        "reports resolved" => $stats["resolved"],
        "items returned to others" => $stats["returned"],
        "tips left" => $stats["tips"]
    ], "row-cols-2 row-cols-lg-4", ["clipboard", "checkCircle", "heart", "bulb"]) ?>

    <h2 class="section-title">Active reports</h2>

    <?php if(count($active) == 0) { ?>
    <?= empty_box("clipboard", "", e($member["Username"]) . " has no active reports right now.") ?>
    <?php } else { ?>
    <div class="item-grid">
        <?php foreach($active as $item) { echo item_card($item); } ?>
    </div>
    <?php } ?>

</div>

<?php require "includes/footer.php"; ?>
