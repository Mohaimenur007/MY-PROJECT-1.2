<?php

// Reports you saved (your watchlist).

require "includes/init.php";
require_login();

$items = Saved::itemsFor(my_id());

$page_title = "Saved reports";
require "includes/header.php";
?>

<div class="container-xl page">

    <?= page_hero("Watchlist", "Saved reports", "Reports you are keeping an eye on. You get an alert when one of them is resolved.") ?>

    <?php if(count($items) == 0) { ?>
    <?= empty_box("bookmark", "Nothing saved yet", "Open any report and press “Save” to keep it here.", '<a href="browse.php" class="btn btn-ink mt-2">Browse the board</a>') ?>
    <?php } else { ?>
    <div class="item-grid">
        <?php foreach($items as $item) { echo item_card($item); } ?>
    </div>
    <?php } ?>

</div>

<?php require "includes/footer.php"; ?>
