<?php

// The Reunited wall: reports that ended with the item back home.

require "includes/init.php";

$items = Item::reunited(30);
$total = Item::publicCount(true);

$page_title = "Reunited";
$page = "reunited";
require "includes/header.php";
?>

<div class="container-xl page">

    <?= page_hero("Back home", "Reunited", $total . " report" . ($total == 1 ? " has" : "s have") . " been resolved on Lostly so far. These are the most recent.") ?>

    <?php if(count($items) == 0) { ?>
    <?= empty_box("heart", "Nothing here yet", "When a report is marked resolved it shows up on this wall.") ?>
    <?php } else { ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach($items as $item) { ?><div class="col"><?= reunited_card($item) ?></div><?php } ?>
    </div>
    <?php } ?>

</div>

<?php require "includes/footer.php"; ?>
