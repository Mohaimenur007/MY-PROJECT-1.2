<?php

// Information pages: page.php?p=about | faq | safety | terms | privacy

require "includes/init.php";

$pages = [

"about" => ["About", "A notice board for a whole country", "Lostly helps people in Bangladesh get their belongings back, and helps finders return what they pick up.", '
<h2>Why Lostly exists</h2>
<p>When something goes missing, the person who lost it and the person who found it usually have no way to reach each other. Posters only cover one street, and social media posts sink within a day. Lostly keeps every lost and found report in one searchable place, sorted by division and district.</p>
<h2>What you can do here</h2>
<ul>
    <li><b>Report</b> a lost or found item with photos, a location and a date.</li>
    <li><b>Search</b> every report without an account.</li>
    <li><b>Get matched</b>: Lostly compares lost and found reports and alerts both sides when two look alike.</li>
    <li><b>Claim</b> an item with proof, and let the reporter approve or reject it.</li>
    <li><b>Talk privately</b> without publishing your phone number.</li>
    <li><b>Print a poster</b> of your report for your neighbourhood.</li>
</ul>
<h2>Who runs it</h2>
<p>Lostly is built and maintained by Group 6 as a community project. It is free to use and carries no advertising.</p>
<p><a class="btn btn-ink" href="report_item.php">Report an item</a> <a class="btn btn-ghost" href="contact.php">Contact us</a></p>'],

"faq" => ["Help", "Questions & answers", "How reporting, matching, claims and privacy work on Lostly.", '
<h2>Using Lostly</h2>
<h3>Do I need an account?</h3>
<p>Not to search or read reports. You need a free account to post a report, send a claim, leave a tip or message someone.</p>
<h3>Does it cost anything?</h3>
<p>No. Everything on Lostly is free.</p>
<h3>What is the LF number on a report?</h3>
<p>Every report has a reference such as <b>LF-00042</b>. Type it into the search box to jump straight to that report. It is also printed on the poster.</p>
<h2>Matching and alerts</h2>
<h3>How does matching work?</h3>
<p>Each lost report is scored against active found reports (and the other way round) using category, division, district, dates and shared words. Strong matches appear on your dashboard and on the report, and both people get an alert.</p>
<h3>Can I be told when something like my item is posted?</h3>
<p>Yes. Set filters or a keyword on the Browse page and press “Alert me about new reports like this”. You can keep up to ten saved searches.</p>
<h2>Claims</h2>
<h3>How do I claim an item?</h3>
<p>Open the report and send a claim describing your proof. The reporter approves or rejects it. Approving a claim marks the report resolved.</p>
<h3>What are “sightings &amp; tips”?</h3>
<p>Public notes anyone logged in can leave on a report, for example “I saw a cat like this near the lake on Friday”. The reporter is alerted.</p>
<h2>Privacy</h2>
<h3>Who can see my phone number and email?</h3>
<p>Only logged-in members, and only while “show my contact details on my reports” is switched on in your account settings. Visitors without an account never see them.</p>
<h3>How do I remove a report?</h3>
<p>Open it from your dashboard and press Delete. Its photos, claims, tips and messages are removed with it.</p>
<p>Still stuck? <a href="contact.php">Send us a message</a>.</p>'],

"safety" => ["Safety", "Safety tips", "Most handovers go smoothly. These habits keep it that way.", '
<h2>Before you meet</h2>
<ul>
    <li><b>Ask for proof.</b> A real owner can describe marks, contents, a lock screen or a serial number. Do not accept “trust me”.</li>
    <li><b>Keep one detail back.</b> If you found something, leave one identifying detail out of your report so only the owner can name it.</li>
    <li><b>Use private messages first.</b> You do not have to share your phone number to arrange a handover.</li>
</ul>
<h2>At the handover</h2>
<ul>
    <li><b>Meet in a public place</b> in daylight: a shop, a bank lobby, a police station front desk.</li>
    <li><b>Bring someone</b> or tell someone where you are going.</li>
    <li><b>Check the item</b> before you mark the report resolved.</li>
</ul>
<h2>Money and rewards</h2>
<ul>
    <li><b>Never pay in advance.</b> Nobody honest needs a “delivery fee”, bKash transfer or deposit before returning an item.</li>
    <li>A reward is the owner\'s choice and is paid in person, after the item is back.</li>
</ul>
<h2>Documents, cards and phones</h2>
<ul>
    <li>For a lost NID, passport or other official document, file a General Diary (GD) at your nearest police station. You will usually need it for a replacement.</li>
    <li>Block lost bank cards and SIMs with your bank and mobile operator straight away.</li>
    <li>Do not post full ID numbers or card numbers in a report.</li>
</ul>
<h2>If something feels wrong</h2>
<p>Use “Report this post to admins” on the report, or the <a href="contact.php">contact form</a>. In an emergency call <b>999</b>.</p>'],

"terms" => ["Legal", "Terms of use", "The rules for using Lostly, in plain language.", '
<p class="text-body-secondary">These terms describe how this project works today. If you run Lostly for the public, have them reviewed for your situation.</p>
<h2>1. Your account</h2>
<p>Give accurate details when you register and keep your password private. You are responsible for what is posted from your account.</p>
<h2>2. What you may post</h2>
<ul>
    <li>Real lost or found items only. No advertising, sales or unrelated posts.</li>
    <li>Photos you are allowed to share, with no private information about other people.</li>
    <li>No false claims on items that are not yours.</li>
</ul>
<h2>3. What Lostly does and does not do</h2>
<p>Lostly lets members find each other. It does not hold items, verify ownership or take part in handovers. Any reward is a private arrangement between members.</p>
<h2>4. Moderation</h2>
<p>Administrators may remove reports or tips and suspend accounts that break these rules. You can report a post from its page.</p>
<h2>5. Responsibility</h2>
<p>Meet and exchange items at your own risk and follow the <a href="page.php?p=safety">safety tips</a>. Lostly is provided as it is, without guarantees that an item will be recovered.</p>
<h2>6. Changes</h2>
<p>These terms may be updated. Continuing to use Lostly after a change means you accept the new terms.</p>'],

"privacy" => ["Legal", "Privacy", "What Lostly stores about you, who can see it and how to remove it.", '
<p class="text-body-secondary">This page describes what the software actually does. If you run Lostly for the public, have it reviewed for your situation.</p>
<h2>What we store</h2>
<ul>
    <li><b>Account:</b> username, email, phone number, division and district, and your password as a one-way hash (never the password itself).</li>
    <li><b>Reports:</b> the details and photos you post.</li>
    <li><b>Activity:</b> claims, tips, private messages, saved reports, saved searches and alerts.</li>
    <li><b>Contact form:</b> the name, email and message you send.</li>
</ul>
<h2>Who can see what</h2>
<ul>
    <li><b>Everyone:</b> your username, your reports and tips, and your public profile (join date and report counts).</li>
    <li><b>Logged-in members:</b> your phone and email on your reports, only while “show my contact details” is on.</li>
    <li><b>The other person in a conversation:</b> your private messages with them.</li>
    <li><b>Administrators:</b> account details and reports, in order to moderate the site.</li>
</ul>
<h2>Cookies and browser storage</h2>
<p>Lostly uses one session cookie to keep you logged in. Your light/dark mode and colour scheme are remembered in your own browser. There are no advertising or tracking cookies.</p>
<h2>Outside services</h2>
<p>None. Fonts, styles and scripts are all served from this site.</p>
<h2>Removing your information</h2>
<p>You can edit your account details and delete your reports at any time. To close your account completely, use the <a href="contact.php">contact form</a>.</p>']

];

$key = get("p");

if(!isset($pages[$key]))
{
    http_response_code(404);
    $page_title = "Not found";
    require "includes/header.php";
    echo '<div class="container-xl page page-narrow">' . empty_box("compass", "Page not found", "That page doesn't exist.", '<a href="index.php" class="btn btn-ink mt-2">Go to the home page</a>') . '</div>';
    require "includes/footer.php";
    exit();
}

list($eyebrow, $heading, $intro, $body) = $pages[$key];

$page_title = $heading;
$page = $key;
require "includes/header.php";
?>

<div class="container-xl page page-narrow">
    <?= page_hero(e($eyebrow), e($heading), e($intro)) ?>
    <article class="panel prose"><?= $body ?></article>
</div>

<?php require "includes/footer.php"; ?>
