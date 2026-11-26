<?php

// One report: photos, details, claims, tips, messages and possible matches.

require "includes/init.php";

$id = get_id();
$item = $id ? Item::find($id) : null;

if(!$item || ($item["IsBanned"] && !is_admin()))
{
    http_response_code(404);
    $page_title = "Not found";
    require "includes/header.php";
    echo '<div class="container-xl page page-narrow">' . empty_box("compass", "Report not found", "That report doesn't exist or was removed.", '<a href="browse.php" class="btn btn-ink mt-2">Browse reports</a>') . '</div>';
    require "includes/footer.php";
    exit();
}

$is_owner = me() && my_id() == $item["UserID"];
$is_lost = $item["Type"] == "Lost";
$logged_in = me() !== null;

// Count a view once per visit, and never the owner's own visits.
if(!$is_owner && empty($_SESSION["viewed"][$id]))
{
    $_SESSION["viewed"][$id] = true;
    Item::addView($id);
    $item["Views"]++;
}

$photos = Item::photos($item);
$comments = Comment::forItem($id);
$claims = $is_owner ? Claim::forItem($id) : [];
$my_claim = $logged_in && !$is_owner ? Claim::mine($id, my_id()) : null;
$is_saved = $logged_in ? Saved::has(my_id(), $id) : false;
$matches = $item["Status"] == "Active" ? Item::matches($item, 6) : [];

$share_url = absolute_url("item_detail.php?id=" . $id);
$share_text = $item["Type"] . ": " . $item["ItemName"] . " (" . $item["District"] . ", " . $item["Division"] . ")";
$back = "item_detail.php?id=" . $id;

$page_title = $item["Type"] . ": " . $item["ItemName"];
$page = "browse";
require "includes/header.php";
?>

<div class="container-xl page">

    <a href="browse.php" class="text-link back-link"><?= ic("arrowLeft") ?>Back to the board</a>

    <div class="row g-4">

        <div class="col-lg-7">
            <article class="panel">

                <div class="detail-media">
                    <?php if(count($photos) > 0) { ?>
                    <img src="<?= e($photos[0]) ?>" alt="<?= e($item["ItemName"]) ?>" class="js-gallery-main">
                    <?php } else { ?>
                    <div class="item-card-placeholder large"><?= illus($item["Category"]) ?></div>
                    <?php } ?>
                    <span class="stamp stamp-<?= strtolower($item["Type"]) ?>"><?= e($item["Type"]) ?></span>
                    <?php if($item["Status"] == "Resolved") { ?><span class="stamp stamp-resolved"><?= ic("check") ?>Resolved</span><?php } ?>
                </div>

                <?php if(count($photos) > 1) { ?>
                <div class="gallery-thumbs">
                    <?php foreach($photos as $i => $path) { ?>
                    <button type="button" class="gallery-thumb js-gallery-thumb<?= $i == 0 ? " active" : "" ?>" aria-label="Show photo <?= $i + 1 ?>"><img src="<?= e($path) ?>" alt=""></button>
                    <?php } ?>
                </div>
                <?php } ?>

                <p class="ref-line">
                    <span class="ref-code"><?= ref_code($id) ?></span>
                    <?php if($is_lost && !empty($item["Reward"]) && $item["Status"] == "Active") { ?>
                    <span class="reward-chip static"><?= ic("gift") ?>Reward <?= taka($item["Reward"]) ?></span>
                    <?php } ?>
                </p>

                <h1 class="detail-title"><?= e($item["ItemName"]) ?></h1>
                <p class="text-body-secondary">
                    Posted by <a href="member.php?id=<?= (int)$item["UserID"] ?>"><b><?= e($item["Username"]) ?></b></a>
                    · <?= time_ago($item["CreatedAt"]) ?> · <?= ic("eye") ?><?= (int)$item["Views"] ?> views
                </p>

                <div class="row row-cols-2 g-3 my-2">
                    <div class="col"><div class="info-item"><span>Category</span><strong><?= category_icon($item["Category"]) ?><?= e($item["Category"]) ?></strong></div></div>
                    <div class="col"><div class="info-item"><span>Date <?= $is_lost ? "lost" : "found" ?></span><strong><?= ic("calendar") ?><?= nice_date($item["EventDate"], "F j, Y") ?></strong></div></div>
                    <div class="col"><div class="info-item"><span>Location</span><strong><?= ic("pin") ?><?= e($item["District"]) ?>, <?= e($item["Division"]) ?></strong></div></div>
                    <div class="col"><div class="info-item"><span>Status</span><strong><?= e($item["Status"]) ?><?= $item["Status"] == "Resolved" && $item["ResolvedAt"] ? " · " . nice_date($item["ResolvedAt"]) : "" ?></strong></div></div>
                </div>

                <?php if($item["Description"]) { ?>
                <h2 class="sub-heading">Description</h2>
                <p class="text-break"><?= nl2br(e($item["Description"])) ?></p>
                <?php } ?>

                <?php if($item["Status"] == "Resolved" && !empty($item["ResolvedNote"])) { ?>
                <div class="thanks-note">
                    <span class="thanks-label">A note from <?= e($item["Username"]) ?></span>
                    <p class="quote mb-0">“<?= nl2br(e($item["ResolvedNote"])) ?>”</p>
                </div>
                <?php } ?>

                <div class="action-bar">
                    <?php if($is_owner) { ?>

                    <a href="report_item.php?id=<?= $id ?>" class="btn btn-ghost btn-sm"><?= ic("edit") ?>Edit</a>
                    <a href="poster.php?id=<?= $id ?>" class="btn btn-ghost btn-sm"><?= ic("printer") ?>Print poster</a>

                    <?php if($item["Status"] == "Active") { ?>
                    <button type="button" class="btn btn-found btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal"><?= ic("checkCircle") ?>Mark as resolved</button>
                    <?php } else { ?>
                    <form method="post" action="ItemController.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="reopen">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn btn-ghost btn-sm">Reopen report</button>
                    </form>
                    <?php } ?>

                    <form method="post" action="delete_item.php" data-confirm="Delete this report? This cannot be undone.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn btn-danger-ghost btn-sm"><?= ic("trash") ?>Delete</button>
                    </form>

                    <?php } else if($logged_in) { ?>

                    <form method="post" action="ItemController.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit" class="btn <?= $is_saved ? "btn-soft" : "btn-ghost" ?> btn-sm"><?= ic("bookmark") ?><?= $is_saved ? "Saved" : "Save" ?></button>
                    </form>

                    <?php } ?>

                    <span class="share-group">
                        <span class="share-label">Share</span>
                        <button type="button" class="btn btn-ghost btn-sm js-copy-link" data-link="<?= e($share_url) ?>"><?= ic("link") ?>Copy link</button>
                        <a class="btn btn-ghost btn-sm" target="_blank" rel="noopener" href="https://wa.me/?text=<?= e(rawurlencode($share_text . " " . $share_url)) ?>"><?= ic("share") ?>WhatsApp</a>
                    </span>
                </div>
            </article>

            <section class="panel" id="tips">
                <div class="section-header">
                    <h2>Sightings &amp; tips <?= badge("count", count($comments)) ?></h2>
                    <span class="text-body-secondary small">Public. Don't post phone numbers here.</span>
                </div>

                <?php if(count($comments) == 0) { ?>
                <p class="text-body-secondary">No tips yet. Seen something that could help? Say where and when.</p>
                <?php } ?>

                <div class="list-rows">
                    <?php foreach($comments as $c) { $can_delete = $logged_in && ($c["UserID"] == my_id() || $is_owner || is_admin()); ?>
                    <div class="list-row align-items-start">
                        <span class="avatar avatar-sm"><?= e(initial($c["Username"])) ?></span>
                        <div class="list-row-info">
                            <strong>
                                <a href="member.php?id=<?= (int)$c["UserID"] ?>"><?= e($c["Username"]) ?></a>
                                <?php if($c["UserID"] == $item["UserID"]) echo badge("resolved", "Reporter"); ?>
                                <span class="text-body-secondary fw-normal small">· <?= time_ago($c["CreatedAt"]) ?></span>
                            </strong>
                            <span class="tip-body"><?= nl2br(e($c["Body"])) ?></span>
                        </div>
                        <?php if($can_delete) { ?>
                        <form method="post" action="ItemController.php" data-confirm="Remove this tip?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_tip">
                            <input type="hidden" name="id" value="<?= (int)$c["ID"] ?>">
                            <button type="submit" class="btn btn-danger-ghost btn-sm" aria-label="Remove tip"><?= ic("x") ?></button>
                        </form>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <?php if($logged_in) { ?>
                <form method="post" action="ItemController.php" class="tip-form js-validate">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="tip">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <textarea class="form-control" name="body" rows="2" maxlength="500" placeholder="e.g. I saw one like this near the lake on Friday evening…" aria-label="Your tip" data-required="Write a little more before posting." data-minlength="3" data-minlength-message="Write a little more before posting."></textarea>
                    <button type="submit" class="btn btn-ink btn-sm">Post tip</button>
                </form>
                <?php } else { ?>
                <p class="mb-0"><a href="<?= e(url("login.php", ["next" => $back])) ?>" class="text-link">Log in</a> to leave a tip.</p>
                <?php } ?>
            </section>
        </div>

        <aside class="col-lg-5">

            <?php if($is_owner) { ?>

            <section class="panel" id="claims">
                <h2>Claims <?= badge("count", count($claims)) ?></h2>

                <?php if(count($claims) == 0) { ?>
                <p class="text-body-secondary mb-0">No one has claimed this yet. You'll get an alert when someone does.</p>
                <?php } ?>

                <?php foreach($claims as $claim) { ?>
                <div class="claim-card">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                        <strong><a href="member.php?id=<?= (int)$claim["ClaimantID"] ?>"><?= e($claim["Username"]) ?></a></strong>
                        <?= badge(strtolower($claim["Status"]), e($claim["Status"])) ?>
                    </div>
                    <p class="quote mb-1">“<?= nl2br(e($claim["Message"])) ?>”</p>
                    <p class="text-body-secondary small"><?= time_ago($claim["CreatedAt"]) ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="messages.php?item=<?= $id ?>&amp;with=<?= (int)$claim["ClaimantID"] ?>" class="btn btn-ghost btn-sm">Message</a>
                        <?php if($claim["Status"] == "Pending") { ?>
                        <form method="post" action="ItemController.php" class="d-flex gap-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="decide">
                            <input type="hidden" name="id" value="<?= (int)$claim["ID"] ?>">
                            <button type="submit" name="decision" value="approve" class="btn btn-found btn-sm">Approve</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-danger-ghost btn-sm">Reject</button>
                        </form>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </section>

            <?php } else if(!$logged_in) { ?>

            <section class="panel panel-alert">
                <h2><?= $is_lost ? "Found this item?" : "Is this yours?" ?></h2>
                <p>Log in to send a claim, message <?= e($item["Username"]) ?> privately or see their contact details.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(url("login.php", ["next" => $back])) ?>" class="btn btn-ink">Log in</a>
                    <a href="registration.php" class="btn btn-ghost">Create free account</a>
                </div>
            </section>

            <?php } else { ?>

            <section class="panel">
                <h2><?= $is_lost ? "Found this item?" : "Is this yours?" ?></h2>

                <?php if($my_claim) { ?>
                <p>Your claim is <?= badge(strtolower($my_claim["Status"]), e($my_claim["Status"])) ?></p>
                <p class="quote mb-0">“<?= nl2br(e($my_claim["Message"])) ?>”</p>
                <?php } else if($item["Status"] == "Active") { ?>
                <p class="text-body-secondary"><?= $is_lost ? "Tell the owner where you found it and describe it so they know it's real." : "Describe something only the owner would know (marks, contents, lock screen…)." ?></p>
                <form method="post" action="ItemController.php" class="js-validate">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="claim">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <textarea class="form-control mb-3" name="claim_message" rows="3" maxlength="1000" placeholder="Your proof or details…" aria-label="Your proof or details" data-required="Please describe your proof" data-minlength="10" data-minlength-message="Please describe your proof in at least 10 characters."></textarea>
                    <div class="form-actions"><button type="submit" class="btn btn-ink w-100"><?= $is_lost ? "I found it — send claim" : "This is mine — send claim" ?></button></div>
                </form>
                <?php } else { ?>
                <p class="text-body-secondary mb-0">This report has been resolved, so it no longer accepts claims.</p>
                <?php } ?>
            </section>

            <section class="panel">
                <h2>Talk to <?= e($item["Username"]) ?></h2>
                <a href="messages.php?item=<?= $id ?>&amp;with=<?= (int)$item["UserID"] ?>" class="btn btn-ink w-100"><?= ic("chat") ?>Send a private message</a>

                <?php if($item["Status"] == "Active" && $item["ShowContact"]) { ?>
                <button type="button" class="btn btn-ghost w-100 mt-3" data-bs-toggle="collapse" data-bs-target="#contact-info" aria-expanded="false" aria-controls="contact-info">Show contact information</button>
                <div id="contact-info" class="collapse">
                    <div class="contact-info">
                        <p><span><?= ic("call") ?>Phone</span> <a href="tel:<?= e($item["Phone"]) ?>"><?= e($item["Phone"]) ?></a></p>
                        <p><span><?= ic("mail") ?>Email</span> <a href="mailto:<?= e($item["Email"]) ?>"><?= e($item["Email"]) ?></a></p>
                    </div>
                </div>
                <?php } else if($item["Status"] == "Active") { ?>
                <p class="text-body-secondary small mt-3 mb-0"><?= e($item["Username"]) ?> prefers to be contacted by private message.</p>
                <?php } ?>
            </section>

            <section class="panel flag-box">
                <button type="button" class="btn btn-link flag-toggle" data-bs-toggle="collapse" data-bs-target="#flag-form" aria-expanded="false" aria-controls="flag-form"><?= ic("flag") ?>Report this post to admins</button>
                <div id="flag-form" class="collapse">
                    <form method="post" action="ItemController.php" class="js-validate mt-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="flag">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="text" class="form-control mb-3" name="reason" maxlength="255" placeholder="What's wrong with it? (spam, fake, offensive…)" aria-label="Reason" data-required="Tell the admins what's wrong">
                        <button type="submit" class="btn btn-danger-ghost btn-sm">Send report</button>
                    </form>
                </div>
            </section>

            <?php } ?>

            <section class="panel safety-box">
                <h2><?= ic("shield") ?>Stay safe</h2>
                <ul class="mb-2">
                    <li>Ask for proof before you meet.</li>
                    <li>Meet in a public place, in daylight.</li>
                    <li>Never pay a fee or deposit in advance.</li>
                </ul>
                <a href="page.php?p=safety" class="text-link">All safety tips <?= ic("arrowRight") ?></a>
            </section>

        </aside>
    </div>

    <?php if(count($matches) > 0) { ?>
    <section class="panel mt-4">
        <div class="section-header">
            <h2><?= ic("sparkle", "h-ic") ?>Possible matches</h2>
            <span class="text-body-secondary"><?= $is_lost ? "Found reports that look like this" : "Lost reports that look like this" ?></span>
        </div>
        <div class="item-grid">
            <?php foreach($matches as $match) { echo item_card($match, $match["Score"]); } ?>
        </div>
    </section>
    <?php } ?>

</div>

<?php if($is_owner && $item["Status"] == "Active") { ?>
<!-- Resolve dialog: optional thank-you note shown on the Reunited wall -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-labelledby="resolveModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" action="ItemController.php" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="resolve">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="resolveModalTitle">Mark as resolved</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Good news! Want to leave a short note? It appears on this report and on the Reunited wall.</p>
                <label for="resolve-note" class="form-label">Thank-you note (optional)</label>
                <textarea class="form-control" id="resolve-note" name="note" rows="3" maxlength="500" placeholder="e.g. A rickshaw puller found it and called me the same evening. Thank you!"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-found">Mark as resolved</button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php require "includes/footer.php"; ?>
