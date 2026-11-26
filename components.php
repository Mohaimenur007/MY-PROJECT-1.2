<?php

// Reusable pieces of HTML: report cards, stat tiles, page banners, badges.

// One report as a card. Pass $score to show a "92% match" bar.
function item_card($item, $score = null)
{
    $type = strtolower($item["Type"]);
    ob_start();
    ?>
    <a href="item_detail.php?id=<?= (int)$item["ID"] ?>" class="item-card card-<?= $type ?>">
        <div class="item-card-media">
            <?php if(!empty($item["ImagePath"])) { ?>
            <img src="<?= e($item["ImagePath"]) ?>" alt="<?= e($item["ItemName"]) ?>" loading="lazy">
            <?php } else { ?>
            <div class="item-card-placeholder"><?= illus($item["Category"]) ?></div>
            <?php } ?>
            <span class="stamp stamp-<?= $type ?>"><?= e($item["Type"]) ?></span>
            <?php if($item["Status"] == "Resolved") { ?>
            <span class="stamp stamp-resolved"><?= ic("check") ?>Resolved</span>
            <?php } else if(!empty($item["Reward"])) { ?>
            <span class="reward-chip"><?= ic("gift") ?>Reward <?= taka($item["Reward"]) ?></span>
            <?php } ?>
        </div>
        <div class="item-card-body">
            <?php if($score !== null) { ?>
            <div class="match-meter" title="How closely this report matches yours">
                <div class="progress flex-grow-1" role="progressbar" aria-label="Match score" aria-valuenow="<?= (int)$score ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: <?= (int)$score ?>%"></div>
                </div>
                <b><?= (int)$score ?>% match</b>
            </div>
            <?php } ?>
            <h3><?= e($item["ItemName"]) ?></h3>
            <p class="item-card-meta">
                <span><?= category_icon($item["Category"]) ?><?= e($item["Category"]) ?></span>
                <span><?= ic("pin") ?><?= e($item["District"]) ?>, <?= e($item["Division"]) ?></span>
            </p>
            <p class="item-card-foot">
                <span><?= ic("calendar") ?><?= nice_date($item["EventDate"]) ?></span>
                <span class="ref-mini"><?= ref_code($item["ID"]) ?></span>
            </p>
        </div>
    </a>
    <?php
    return ob_get_clean();
}

// A row of big-number tiles. $tiles: label => number; $icons: one icon name per tile.
function stat_tiles($tiles, $cols = "row-cols-2 row-cols-lg-4", $icons = [])
{
    $html = '<div class="row ' . $cols . ' g-3 stat-strip">';
    $i = 0;

    foreach($tiles as $label => $value)
    {
        $html .= '<div class="col"><div class="stat-tile">'
            . (isset($icons[$i]) ? '<span class="stat-ic">' . ic($icons[$i]) . '</span>' : '')
            . '<strong class="js-count" data-count="' . (int)$value . '">' . (int)$value . '</strong>'
            . '<span>' . e($label) . '</span></div></div>';
        $i++;
    }

    return $html . '</div>';
}

// A resolved report as a testimonial card.
function reunited_card($item)
{
    $note = $item["ResolvedNote"];

    if(!$note)
    {
        $note = $item["Type"] == "Lost"
            ? "My " . strtolower($item["ItemName"]) . " is back home, thanks to the Lostly community."
            : "The owner of this " . strtolower($item["ItemName"]) . " got it back.";
    }

    return '<a class="reunited-card h-100" href="item_detail.php?id=' . (int)$item["ID"] . '">'
        . '<span class="stamp stamp-resolved">' . ic("check") . 'Reunited</span>'
        . '<span class="q-mark">“</span><span class="quote">' . e(shorten($note, 170)) . '</span>'
        . '<span class="reunited-foot"><span class="reunited-icon">' . illus($item["Category"]) . '</span><div>'
        . '<strong>' . e($item["ItemName"]) . '</strong>'
        . '<small>' . e($item["Username"]) . ' · ' . e($item["District"]) . ($item["ResolvedAt"] ? ' · ' . nice_date($item["ResolvedAt"]) : '') . '</small>'
        . '</div></span></a>';
}

function empty_box($icon, $title, $text, $extra = "")
{
    return '<div class="empty"><span class="empty-icon">' . ic($icon) . '</span>'
        . ($title != "" ? '<h2>' . $title . '</h2>' : '')
        . ($text != "" ? '<p>' . $text . '</p>' : '')
        . $extra . '</div>';
}

// The banner at the top of inner pages. $dark = true for the espresso version.
function page_hero($eyebrow, $title, $text = "", $extra = "", $dark = false)
{
    return '<section class="page-hero' . ($dark ? ' is-dark' : '') . '">'
        . '<p class="eyebrow">' . $eyebrow . '</p><h1>' . $title . '</h1>'
        . ($text != "" ? '<p>' . $text . '</p>' : '')
        . $extra . '</section>';
}

function badge($class, $text)
{
    return '<span class="badge rounded-pill badge-' . $class . '">' . $text . '</span>';
}

// <option> tags for a select. $first is the empty "All ..." option.
function options($list, $current, $first = "")
{
    $html = $first != "" ? '<option value="">' . e($first) . '</option>' : '';

    foreach($list as $value)
    {
        $html .= '<option value="' . e($value) . '"' . selected($value, $current) . '>' . e($value) . '</option>';
    }

    return $html;
}

// Suggestions for a District field; js/main.js refills it when the Division changes.
function district_datalist()
{
    global $districts;
    return '<datalist id="district-list" data-districts="' . e(json_encode($districts)) . '"></datalist>';
}
