<?php

// A printable A4 poster of one of your reports.

require "includes/init.php";
require_login();

$item = Item::find(get_id());

if(!$item || $item["UserID"] != my_id())
{
    flash("You can only print posters for your own reports.", "error");
    redirect("dashboard.php");
}

$is_lost = $item["Type"] == "Lost";
$link = absolute_url("item_detail.php?id=" . (int)$item["ID"]);

$page_title = $item["Type"] . ": " . $item["ItemName"] . " poster";
$body_class = "poster-mode";
require "includes/header.php";
?>

<div class="poster-page">
    <div class="toolbar">
        <a href="item_detail.php?id=<?= (int)$item["ID"] ?>">← Back to the report</a>
        <button type="button" onclick="window.print()">Print poster</button>
    </div>

    <div class="poster-sheet">
        <div class="banner <?= $is_lost ? "is-lost" : "is-found" ?>"><?= $is_lost ? "LOST" : "FOUND" ?></div>

        <h1><?= e($item["ItemName"]) ?></h1>

        <?php if($item["ImagePath"]) { ?>
        <img class="photo" src="<?= e($item["ImagePath"]) ?>" alt="<?= e($item["ItemName"]) ?>">
        <?php } else { ?>
        <div class="photo-emoji"><?= illus($item["Category"]) ?></div>
        <?php } ?>

        <div class="facts">
            <div><span><?= $is_lost ? "Last seen" : "Found at" ?></span><strong><?= e($item["District"]) ?>, <?= e($item["Division"]) ?></strong></div>
            <div><span>Date</span><strong><?= nice_date($item["EventDate"], "F j, Y") ?></strong></div>
        </div>

        <?php if($item["Description"]) { ?>
        <p class="description"><?= nl2br(e(shorten($item["Description"], 400))) ?></p>
        <?php } ?>

        <?php if($is_lost && !empty($item["Reward"])) { ?>
        <p class="reward">REWARD <?= taka($item["Reward"]) ?></p>
        <?php } ?>

        <div class="contact">
            <p><?= $is_lost ? "If you have seen or found it, please call" : "If this is yours, please call" ?> <b><?= e($item["Username"]) ?></b></p>
            <p class="phone"><?= e($item["Phone"]) ?></p>
            <small>or search <b><?= ref_code($item["ID"]) ?></b> on Lostly · <?= e($link) ?></small>
        </div>
    </div>
</div>

<?php require "includes/footer.php"; ?>
