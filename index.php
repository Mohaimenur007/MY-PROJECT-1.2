<?php

// Home page.

require "includes/init.php";

$recent = Item::recent(8);
$reunited = Item::reunited(3);
$category_counts = Item::activeCounts("Category");
$division_counts = Item::activeCounts("Division");
$total_reports = Item::publicCount();
$total_reunited = Item::publicCount(true);
$total_members = User::count(true);
$members = User::sample(5);

// The hero card shows the strongest real match on the board.
$demo = null;

foreach($recent as $item)
{
    if($item["Type"] != "Lost") continue;

    $best = Item::matches($item, 1);

    if(count($best) > 0 && (!$demo || $best[0]["Score"] > $demo["score"]))
    {
        $demo = ["lost" => $item, "found" => $best[0], "score" => $best[0]["Score"]];
    }
}

if(!$demo)
{
    $demo = [
        "lost" => ["ItemName" => "Black leather wallet", "District" => "Dhanmondi", "Category" => "Bags & Wallets"],
        "found" => ["ItemName" => "Leather wallet, black", "District" => "Dhanmondi", "Category" => "Bags & Wallets"],
        "score" => 92
    ];
}

$faq = [
    ["Does it cost anything?", "No. Posting, searching, claiming and messaging are all free."],
    ["Who can see my phone number?", "Only logged-in members, and only if you leave “show my contact details” switched on in your account settings. With it off, people reach you through private messages."],
    ["How do I know a claim is real?", "Every claim must include proof in words: something only the owner or finder would know. You decide whether to approve it, and you can chat first."]
];

$page_title = "Lost & found for Bangladesh";
$page = "home";
require "includes/header.php";
?>

<div class="container-xl landing">

    <section class="row align-items-center gy-5 gx-lg-5 hero">
        <div class="col-lg-6">
            <span class="hero-pill"><span class="pulse"></span>Free community service · all 64 districts</span>
            <h1>Lost something?<br>Let's bring it <span class="hl-word">home.</span></h1>
            <p class="hero-sub">Lostly is the lost &amp; found network for Bangladesh. Report what you lost or found, get matched automatically, and talk privately with the other person.</p>

            <form method="get" action="browse.php" class="hero-search" role="search">
                <?= ic("search") ?>
                <input type="search" class="form-control form-control-lg" name="search" placeholder="Search “wallet”, “Mirpur” or LF-00003" aria-label="Search reports">
                <button type="submit" class="btn btn-ink btn-lg">Search</button>
            </form>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="report_item.php?type=lost" class="btn btn-lost"><?= ic("plus") ?>Report a lost item</a>
                <a href="report_item.php?type=found" class="btn btn-found"><?= ic("plus") ?>Report a found item</a>
            </div>

            <ul class="hero-trust">
                <li><?= ic("checkCircle") ?>Free, no ads</li>
                <li><?= ic("zap") ?>Automatic matching</li>
                <li><?= ic("lock") ?>Private messaging</li>
            </ul>

            <div class="hero-proof">
                <span class="avatar-stack">
                    <?php foreach($members as $m) { ?><span><?= e(initial($m["Username"])) ?></span><?php } ?>
                </span>
                <span><b><?= $total_members ?> members</b> helping across Bangladesh</span>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="hero-visual" aria-hidden="true">
                <div class="hv-pills"><i></i><i></i><i></i><i></i><i></i></div>
                <div class="hv-card">
                    <div class="hv-head">
                        <span class="hv-icon"><?= ic("sparkle") ?></span>
                        <div><b>Possible match found</b><small>Compared by category, area, date and wording</small></div>
                        <span class="hv-score"><?= (int)$demo["score"] ?>%</span>
                    </div>
                    <div class="hv-row">
                        <span class="hv-thumb is-lost"><?= illus($demo["lost"]["Category"]) ?></span>
                        <div><small><?= badge("lost", "Lost") ?> <?= e($demo["lost"]["District"]) ?></small><strong><?= e($demo["lost"]["ItemName"]) ?></strong></div>
                    </div>
                    <div class="hv-link"><span></span></div>
                    <div class="hv-row">
                        <span class="hv-thumb is-found"><?= illus($demo["found"]["Category"]) ?></span>
                        <div><small><?= badge("found", "Found") ?> <?= e($demo["found"]["District"]) ?></small><strong><?= e($demo["found"]["ItemName"]) ?></strong></div>
                    </div>
                    <div class="hv-bar"><i style="width: <?= (int)$demo["score"] ?>%"></i></div>
                    <div class="hv-actions">
                        <span class="btn btn-ink btn-sm"><?= ic("usercheck") ?>Send claim</span>
                        <span class="btn btn-ghost btn-sm"><?= ic("chat") ?>Message</span>
                    </div>
                </div>
                <div class="hv-float hv-f1"><span class="hv-mini is-brand"><?= ic("bell") ?></span><div><b>New match alert</b><small>Sent to both people</small></div></div>
                <div class="hv-float hv-f2"><span class="hv-mini is-found"><?= ic("checkCircle") ?></span><div><b><?= $total_reunited ?> reunited</b><small>and counting</small></div></div>
            </div>
        </div>
    </section>

    <?php if(count($recent) > 0) { ?>
    <!-- Scrolling strip of the newest reports (decorative; the same reports are listed below) -->
    <section class="ticker" aria-hidden="true">
        <div class="ticker-track">
            <?php for($loop = 0; $loop < 2; $loop++) { ?>
            <div class="ticker-group">
                <?php foreach($recent as $item) { ?>
                <span class="ticker-item"><b class="ticker-<?= strtolower($item["Type"]) ?>"><?= e($item["Type"]) ?></b><?= e($item["ItemName"]) ?><small><?= ic("pin") ?><?= e($item["District"]) ?></small></span>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </section>
    <?php } ?>

    <section class="stats-band">
        <?php foreach([[$total_reports, "reports posted"], [$total_reunited, "items reunited"], [$total_members, "members helping"], [64, "districts covered"]] as $stat) { ?>
        <div><strong class="js-count" data-count="<?= (int)$stat[0] ?>"><?= (int)$stat[0] ?></strong><span><?= $stat[1] ?></span></div>
        <?php } ?>
    </section>

    <section>
        <div class="section-header">
            <h2 class="section-title mb-0">Browse by category</h2>
            <a href="browse.php" class="text-link">See all reports <?= ic("arrowRight") ?></a>
        </div>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php foreach($categories as $cat) { $n = isset($category_counts[$cat]) ? $category_counts[$cat] : 0; ?>
            <div class="col">
                <a class="category-tile" href="<?= e(url("browse.php", ["category" => $cat])) ?>">
                    <span class="category-icon"><?= illus($cat) ?></span>
                    <div><strong><?= e($cat) ?></strong><small><?= plural($n, "active report") ?></small></div>
                    <?= ic("arrowRight", "tile-arrow") ?>
                </a>
            </div>
            <?php } ?>
        </div>
    </section>

    <?php if(count($recent) > 0) { ?>
    <section>
        <div class="section-header">
            <h2 class="section-title mb-0">Fresh on the board</h2>
            <a href="browse.php" class="text-link">Browse everything <?= ic("arrowRight") ?></a>
        </div>
        <div class="item-grid">
            <?php foreach($recent as $item) { echo item_card($item); } ?>
        </div>
    </section>
    <?php } ?>

    <section>
        <div class="section-header"><h2 class="section-title mb-0">How it works</h2></div>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $steps = [
                ["clipboard", "Pin a notice", "Describe the item, add up to four photos and say where it happened. Lost something valuable? Add a reward."],
                ["sparkle", "Get matched", "Lostly compares every lost report with every found report, and alerts you about matches and saved searches."],
                ["chat", "Claim &amp; chat", "Send a claim with proof, message each other privately, and mark it resolved when it's home."]
            ];
            foreach($steps as $i => $step) { ?>
            <div class="col">
                <div class="how-step h-100">
                    <span class="how-big">0<?= $i + 1 ?></span>
                    <span class="how-num"><?= ic($step[0]) ?></span>
                    <span class="how-step-no">Step <?= $i + 1 ?></span>
                    <h3><?= $step[1] ?></h3>
                    <p><?= $step[2] ?></p>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>

    <section>
        <div class="section-header"><h2 class="section-title mb-0">Where are you looking?</h2></div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach($divisions as $div) { ?>
            <a class="division-chip" href="<?= e(url("browse.php", ["division" => $div])) ?>"><?= ic("pin") ?><?= e($div) ?> <b><?= isset($division_counts[$div]) ? $division_counts[$div] : 0 ?></b></a>
            <?php } ?>
        </div>
    </section>

    <?php if(count($reunited) > 0) { ?>
    <section>
        <div class="section-header">
            <h2 class="section-title mb-0">Recently reunited</h2>
            <a href="reunited.php" class="text-link">All reunited items <?= ic("arrowRight") ?></a>
        </div>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach($reunited as $item) { ?><div class="col"><?= reunited_card($item) ?></div><?php } ?>
        </div>
    </section>
    <?php } ?>

    <section class="row gy-4 gx-lg-5">
        <div class="col-lg-5">
            <h2 class="section-title">Questions people ask</h2>
            <p class="text-body-secondary">The short version. The <a href="page.php?p=faq">full list is here</a>, and <a href="page.php?p=safety">safety tips</a> are worth two minutes before you meet anyone.</p>
        </div>
        <div class="col-lg-7">
            <div class="accordion" id="homeFaq">
                <?php foreach($faq as $i => $q) { ?>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button<?= $i ? " collapsed" : "" ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>" aria-expanded="<?= $i ? "false" : "true" ?>" aria-controls="faq<?= $i ?>"><?= $q[0] ?></button>
                    </h3>
                    <div id="faq<?= $i ?>" class="accordion-collapse collapse<?= $i ? "" : " show" ?>" data-bs-parent="#homeFaq">
                        <div class="accordion-body"><?= $q[1] ?></div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <?php if(!me()) { ?>
    <section class="cta-band">
        <span class="cta-pills" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
        <div>
            <h2>Lost or found something today?</h2>
            <p class="mb-0">It takes about a minute to pin a notice.</p>
        </div>
        <a href="registration.php" class="btn btn-ink btn-lg">Create free account</a>
    </section>
    <?php } ?>

</div>

<?php require "includes/footer.php"; ?>
