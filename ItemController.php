<?php

// Handles the buttons and small forms on a report:
// claim, approve/reject a claim, tips, flags, save, resolve, reopen and saved searches.
// Every request is a POST with a CSRF token and an "action" value.

require "includes/init.php";

if(!is_post())
{
    redirect("browse.php");
}

check_csrf();
require_login();

$action = post("action");
$id = post_id();

switch($action)
{
    // ---- send a claim ("this is mine" / "I found it") ----
    case "claim":
        $item = Item::find($id);
        $message = post("claim_message");

        if(!$item || $item["IsBanned"]) redirect("browse.php");
        $back = "item_detail.php?id=" . $id;

        if($item["UserID"] == my_id()) { flash("You can't claim your own report.", "error"); redirect($back); }
        if($item["Status"] != "Active") { flash("This report no longer accepts claims.", "error"); redirect($back); }
        if(Claim::mine($id, my_id())) { flash("You have already sent a claim on this report.", "error"); redirect($back); }
        if(text_length($message) < 10 || text_length($message) > 1000) { flash("Please describe your proof in at least 10 characters.", "error"); redirect($back); }

        Claim::create($id, my_id(), $message);
        Notification::send($item["UserID"], "claim", me()["Username"] . " sent a claim on “" . $item["ItemName"] . "”.", "item_detail.php?id=" . $id . "#claims");
        flash("Claim sent. " . $item["Username"] . " will review it.");
        redirect($back);

    // ---- the report owner approves or rejects a claim ----
    case "decide":
        $claim = Claim::find($id);
        $item = $claim ? Item::find($claim["ItemID"]) : null;

        if(!$claim || !$item || $item["UserID"] != my_id() || $claim["Status"] != "Pending")
        {
            flash("That claim can't be changed.", "error");
            redirect($item ? "item_detail.php?id=" . $item["ID"] : "dashboard.php");
        }

        $back = "item_detail.php?id=" . $item["ID"];

        if(post("decision") == "approve")
        {
            Claim::setStatus($claim["ID"], "Approved");
            Notification::send($claim["ClaimantID"], "claim", "Your claim on “" . $item["ItemName"] . "” was approved. Message " . me()["Username"] . " to arrange the handover.", "messages.php?item=" . $item["ID"] . "&with=" . $item["UserID"]);

            // Everyone else who claimed it is told it went to someone else.
            foreach(Claim::pendingFor($item["ID"]) as $other)
            {
                Claim::setStatus($other["ID"], "Rejected");
                Notification::send($other["ClaimantID"], "claim", "Your claim on “" . $item["ItemName"] . "” was rejected.", $back);
            }

            resolve_item($item, null);
            flash("Claim approved and report marked as resolved.");
        }
        else
        {
            Claim::setStatus($claim["ID"], "Rejected");
            Notification::send($claim["ClaimantID"], "claim", "Your claim on “" . $item["ItemName"] . "” was rejected.", $back);
            flash("Claim rejected.");
        }

        redirect($back);

    // ---- public tip / sighting ----
    case "tip":
        $item = Item::find($id);
        $body = post("body");

        if(!$item || $item["IsBanned"]) redirect("browse.php");

        if(text_length($body) < 3 || text_length($body) > 500)
        {
            flash("Write a little more before posting.", "error");
        }
        else
        {
            Comment::create($id, my_id(), $body);

            if($item["UserID"] != my_id())
            {
                Notification::send($item["UserID"], "tip", me()["Username"] . " left a tip on “" . $item["ItemName"] . "”.", "item_detail.php?id=" . $id . "#tips");
            }

            flash("Thanks! Your tip was posted.");
        }

        header("Location: item_detail.php?id=" . $id . "#tips");
        exit();

    case "delete_tip":
        $comment = Comment::find($id);
        $item = $comment ? Item::find($comment["ItemID"]) : null;

        if($comment && ($comment["UserID"] == my_id() || ($item && $item["UserID"] == my_id()) || is_admin()))
        {
            Comment::delete($id);

            if(is_admin() && $comment["UserID"] != my_id() && (!$item || $item["UserID"] != my_id()))
            {
                AdminLog::add(my_id(), "Removed tip", "On “" . ($item ? $item["ItemName"] : "?") . "”");
            }

            flash("Tip removed.");
        }

        redirect($item ? "item_detail.php?id=" . $item["ID"] : "browse.php");

    // ---- report a post to the admins ----
    case "flag":
        $item = Item::find($id);
        $reason = shorten(post("reason"), 255);

        if(!$item) redirect("browse.php");
        $back = "item_detail.php?id=" . $id;

        if($reason == "") { flash("Tell the admins what's wrong.", "error"); redirect($back); }
        if(Flag::exists($id, my_id())) { flash("You already reported this post. An admin will look at it.", "error"); redirect($back); }

        Flag::create($id, my_id(), $reason);

        foreach(User::admins() as $admin)
        {
            Notification::send($admin["ID"], "admin", "A post was reported: “" . $item["ItemName"] . "”.", "admin.php?tab=flags");
        }

        flash("Thanks. An admin will review this post.");
        redirect($back);

    // ---- save / unsave a report (watchlist) ----
    case "save":
        $item = Item::find($id);
        if(!$item) redirect("browse.php");

        $now_saved = Saved::toggle(my_id(), $id);
        flash($now_saved ? "Saved. You'll get an alert if it's resolved." : "Removed from your saved reports.");
        redirect("item_detail.php?id=" . $id);

    // ---- the owner marks the report resolved, with an optional thank-you note ----
    case "resolve":
        $item = Item::find($id);

        if($item && $item["UserID"] == my_id() && $item["Status"] == "Active")
        {
            $note = shorten(post("note"), 500);
            resolve_item($item, $note != "" ? $note : null);
            flash("Marked as resolved. Wonderful news!");
        }

        redirect("item_detail.php?id=" . $id);

    case "reopen":
        $item = Item::find($id);

        if($item && $item["UserID"] == my_id())
        {
            Item::reopen($id);
            flash("Your report is active again.");
        }

        redirect("item_detail.php?id=" . $id);

    // ---- "alert me about new reports like this" from the Browse page ----
    case "save_search":
        $search = [
            "keyword" => shorten(post("keyword"), 100),
            "type" => in_array(post("type"), ["Lost", "Found"], true) ? post("type") : "",
            "category" => in_array(post("category"), $categories, true) ? post("category") : "",
            "division" => in_array(post("division"), $divisions, true) ? post("division") : ""
        ];

        if(SearchAlert::exists(my_id(), $search))
        {
            flash("You already have an alert for this search.", "error");
        }
        else if(count(SearchAlert::forUser(my_id())) >= config("max_search_alerts"))
        {
            flash("You can keep up to " . config("max_search_alerts") . " saved searches. Remove one first.", "error");
        }
        else
        {
            SearchAlert::create(my_id(), $search);
            flash("Saved. We'll alert you when a matching report is posted.");
        }

        back("browse.php");
}

redirect("browse.php");


// Marks a report resolved and tells everyone who saved it.
function resolve_item($item, $note)
{
    Item::resolve($item["ID"], $note);

    foreach(Saved::watchers($item["ID"]) as $row)
    {
        if($row["UserID"] != $item["UserID"])
        {
            Notification::send($row["UserID"], "saved", "A report you saved, “" . $item["ItemName"] . "”, was resolved.", "item_detail.php?id=" . $item["ID"]);
        }
    }
}
