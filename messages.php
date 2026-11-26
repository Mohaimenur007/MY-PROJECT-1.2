<?php

// Private messages.
//   messages.php                         -> your inbox
//   messages.php?item=5&with=3           -> the conversation with member 3 about report 5
//   messages.php?item=5&with=3&after=9   -> new messages as JSON (used for live updates)

require "includes/init.php";
require_login();

$item_id = is_post() ? post_id("item") : get_id("item");
$other_id = is_post() ? post_id("with") : get_id("with");

// ---- inbox ----
if(!$item_id && !$other_id)
{
    $threads = Message::inbox(my_id());

    $page_title = "Messages";
    $page = "messages";
    require "includes/header.php";
    ?>
    <div class="container-xl page page-narrow">
        <?= page_hero("Inbox", "Messages", "Private conversations about lost and found items.") ?>
        <section class="panel">
            <?php if(count($threads) == 0) { ?>
            <?= empty_box("chat", "", "No conversations yet. Open a notice and press “Send a private message”.") ?>
            <?php } else { ?>
            <div class="list-rows">
                <?php foreach($threads as $t) { ?>
                <a class="list-row list-row-link<?= $t["unread"] > 0 ? " unread" : "" ?>" href="messages.php?item=<?= (int)$t["item_id"] ?>&amp;with=<?= (int)$t["other_id"] ?>">
                    <span class="avatar"><?= e(initial($t["other_name"])) ?></span>
                    <div class="list-row-info">
                        <strong><?= e($t["other_name"]) ?> <?php if($t["unread"] > 0) echo badge("count", (int)$t["unread"] . " new"); ?></strong>
                        <span>About: <?= e($t["item_name"]) ?> (<?= e($t["type"]) ?>)</span>
                        <span class="quote"><?= $t["last_mine"] ? "You: " : "" ?><?= e(shorten($t["last_body"], 90)) ?></span>
                    </div>
                    <span class="text-body-secondary small"><?= time_ago($t["last_time"]) ?></span>
                </a>
                <?php } ?>
            </div>
            <?php } ?>
        </section>
    </div>
    <?php
    require "includes/footer.php";
    exit();
}

// ---- one conversation ----
// You can talk about a report if you own it, or with its owner.
$item = Item::find($item_id);
$other = User::find($other_id);
$allowed = $item && $other && $other_id != my_id() && (my_id() == $item["UserID"] || $other_id == $item["UserID"]);

if(get("after") !== "")
{
    // Live updates for the open chat window.
    header("Content-Type: application/json");

    if(!$allowed)
    {
        echo "[]";
        exit();
    }

    Message::markRead($item_id, my_id(), $other_id);
    $rows = [];

    foreach(Message::thread($item_id, my_id(), $other_id, max(0, (int)get("after"))) as $m)
    {
        $rows[] = ["id" => (int)$m["ID"], "mine" => $m["SenderID"] == my_id(), "body" => $m["Body"], "time" => nice_date($m["CreatedAt"], "M j, g:i a")];
    }

    echo json_encode($rows);
    exit();
}

if(!$allowed)
{
    $page_title = "Conversation";
    $page = "messages";
    require "includes/header.php";
    echo '<div class="container-xl page page-narrow">' . empty_box("chat", "Conversation not available", "This conversation isn't available.", '<a href="messages.php" class="btn btn-ink mt-2">Back to messages</a>') . '</div>';
    require "includes/footer.php";
    exit();
}

$thread_link = "messages.php?item=" . $item_id . "&with=" . $other_id;

if(is_post())
{
    check_csrf();
    $body = post("body");

    if($body == "" || text_length($body) > 2000)
    {
        flash("Type a message first (up to 2000 characters).", "error");
    }
    else
    {
        Message::send($item_id, my_id(), $other_id, $body);
    }

    redirect("messages.php", ["item" => $item_id, "with" => $other_id]);
}

Message::markRead($item_id, my_id(), $other_id);
$messages = Message::thread($item_id, my_id(), $other_id);
$last_id = count($messages) > 0 ? (int)$messages[count($messages) - 1]["ID"] : 0;

$page_title = "Chat with " . $other["Username"];
$page = "messages";
require "includes/header.php";
?>

<div class="container-xl page page-narrow">

    <a href="messages.php" class="text-link back-link"><?= ic("arrowLeft") ?>All conversations</a>

    <section class="panel chat">
        <div class="chat-head">
            <span class="avatar"><?= e(initial($other["Username"])) ?></span>
            <div>
                <strong><?= e($other["Username"]) ?></strong>
                <span class="text-body-secondary">about <a href="item_detail.php?id=<?= $item_id ?>"><?= e($item["ItemName"]) ?></a> <?= badge(strtolower($item["Type"]), e($item["Type"])) ?></span>
            </div>
        </div>

        <div class="chat-body js-chat" data-url="<?= e($thread_link) ?>" data-last="<?= $last_id ?>">
            <?php if(count($messages) == 0) { ?>
            <p class="text-body-secondary chat-empty js-chat-empty">No messages yet. Say hello and explain why you're writing.</p>
            <?php } ?>
            <?php foreach($messages as $m) { ?>
            <div class="bubble <?= $m["SenderID"] == my_id() ? "mine" : "theirs" ?>">
                <p><?= nl2br(e($m["Body"])) ?></p>
                <time><?= nice_date($m["CreatedAt"], "M j, g:i a") ?></time>
            </div>
            <?php } ?>
        </div>

        <form method="post" action="messages.php" class="chat-form js-validate">
            <?= csrf_field() ?>
            <input type="hidden" name="item" value="<?= $item_id ?>">
            <input type="hidden" name="with" value="<?= $other_id ?>">
            <textarea class="form-control js-enter-submit" name="body" rows="2" maxlength="2000" placeholder="Write a message…" aria-label="Message" data-required="Type a message first."></textarea>
            <button type="submit" class="btn btn-ink">Send</button>
        </form>
    </section>

</div>

<?php require "includes/footer.php"; ?>
