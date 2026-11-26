<?php

// Browse and search every report.

require "includes/init.php";

$filters = [
    "search" => shorten(get("search"), 100),
    "type" => in_array(get("type"), ["Lost", "Found"], true) ? get("type") : "",
    "division" => in_array(get("division"), $divisions, true) ? get("division") : "",
    "category" => in_array(get("category"), $categories, true) ? get("category") : "",
    "status" => in_array(get("status"), ["Active", "Resolved", "All"], true) ? get("status") : "Active",
    "sort" => in_array(get("sort"), ["newest", "oldest", "date", "popular"], true) ? get("sort") : "newest"
];

// A reference such as LF-00012 jumps straight to that report.
if(preg_match('/^LF-?0*([1-9][0-9]*)$/i', $filters["search"], $m))
{
    $found = Item::find((int)$m[1]);

    if($found && !$found["IsBanned"])
    {
        redirect("item_detail.php", ["id" => (int)$m[1]]);
    }
}

$per_page = config("per_page");
$current = max(1, (int)get("page"));
$result = Item::search($filters, $current, $per_page);
$total = $result["total"];
$pages = max(1, (int)ceil($total / $per_page));

if($current > $pages)
{
    $current = $pages;
    $result = Item::search($filters, $current, $per_page);
}

$items = $result["items"];

// Link to another page of results, keeping every filter.
$page_link = function ($number) use ($filters) {
    $q = $filters;
    if($q["status"] == "Active") $q["status"] = "";
    if($q["sort"] == "newest") $q["sort"] = "";
    $q["page"] = $number > 1 ? $number : "";
    return e(url("browse.php", $q));
};

$has_filter = $filters["search"] . $filters["type"] . $filters["division"] . $filters["category"] != "";

$page_title = "Browse";
$page = "browse";
require "includes/header.php";
?>

<div class="container-xl page">

    <?= page_hero("Community board", "Browse Lost &amp; Found", "Search for an item, check reports from your area, and connect with the person who posted it.") ?>

    <form method="get" action="browse.php" class="panel filter-bar">
        <div class="row g-3">
            <div class="col-md-8 col-lg-9">
                <input type="search" class="form-control" name="search" placeholder="Search item, description, location or LF number…" value="<?= e($filters["search"]) ?>" aria-label="Search">
            </div>
            <div class="col-md-4 col-lg-3 d-flex gap-3">
                <button type="submit" class="btn btn-ink flex-grow-1">Search</button>
                <a href="browse.php" class="btn btn-ghost" title="Clear filters">Reset</a>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <select class="form-select js-autosubmit" name="type" aria-label="Type"><?= options(["Lost", "Found"], $filters["type"], "All Types") ?></select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <select class="form-select js-autosubmit" name="division" aria-label="Division"><?= options($divisions, $filters["division"], "All Divisions") ?></select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <select class="form-select js-autosubmit" name="category" aria-label="Category"><?= options($categories, $filters["category"], "All Categories") ?></select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <select class="form-select js-autosubmit" name="status" aria-label="Status">
                    <option value="Active"<?= selected($filters["status"], "Active") ?>>Active only</option>
                    <option value="Resolved"<?= selected($filters["status"], "Resolved") ?>>Resolved only</option>
                    <option value="All"<?= selected($filters["status"], "All") ?>>Active + resolved</option>
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <select class="form-select js-autosubmit" name="sort" aria-label="Sort by">
                    <option value="newest"<?= selected($filters["sort"], "newest") ?>>Newest first</option>
                    <option value="oldest"<?= selected($filters["sort"], "oldest") ?>>Oldest first</option>
                    <option value="date"<?= selected($filters["sort"], "date") ?>>Latest lost/found date</option>
                    <option value="popular"<?= selected($filters["sort"], "popular") ?>>Most viewed</option>
                </select>
            </div>
        </div>
    </form>

    <?php if($has_filter) { ?>
    <form method="post" action="ItemController.php" class="save-search">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_search">
        <input type="hidden" name="back" value="<?= e(current_page()) ?>">
        <input type="hidden" name="keyword" value="<?= e($filters["search"]) ?>">
        <input type="hidden" name="type" value="<?= e($filters["type"]) ?>">
        <input type="hidden" name="division" value="<?= e($filters["division"]) ?>">
        <input type="hidden" name="category" value="<?= e($filters["category"]) ?>">
        <span>Not here yet?</span>
        <button type="submit" class="btn btn-ghost btn-sm"><?= ic("bell") ?>Alert me about new reports like this</button>
    </form>
    <?php } ?>

    <?php if($total == 0) { ?>

    <?= empty_box("search", "No items found", "Try a different keyword or remove one of the filters.") ?>

    <?php } else { ?>

    <p class="browse-summary"><b><?= $total ?></b> report<?= $total == 1 ? "" : "s" ?> · page <?= $current ?> of <?= $pages ?></p>

    <div class="item-grid">
        <?php foreach($items as $item) { echo item_card($item); } ?>
    </div>

    <?php if($pages > 1) { ?>
    <nav aria-label="Pages" class="mt-5">
        <ul class="pagination justify-content-center flex-wrap">
            <li class="page-item <?= $current == 1 ? "disabled" : "" ?>"><a class="page-link" href="<?= $page_link(max(1, $current - 1)) ?>"><?= ic("arrowLeft") ?>Prev</a></li>
            <?php for($i = 1; $i <= $pages; $i++) { ?>
                <?php if($i == 1 || $i == $pages || abs($i - $current) <= 2) { ?>
                <li class="page-item <?= $i == $current ? "active" : "" ?>"><a class="page-link" href="<?= $page_link($i) ?>"<?= $i == $current ? ' aria-current="page"' : "" ?>><?= $i ?></a></li>
                <?php } else if(abs($i - $current) == 3) { ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php } ?>
            <?php } ?>
            <li class="page-item <?= $current == $pages ? "disabled" : "" ?>"><a class="page-link" href="<?= $page_link(min($pages, $current + 1)) ?>">Next <?= ic("arrowRight") ?></a></li>
        </ul>
    </nav>
    <?php } ?>

    <?php } ?>

</div>

<?php require "includes/footer.php"; ?>
