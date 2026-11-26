<?php

// Deletes one of your own reports (with its photos, claims, tips and messages).

require "includes/init.php";

if(!is_post())
{
    redirect("dashboard.php");
}

check_csrf();
require_login();

$item = Item::find(post_id());

if($item && $item["UserID"] == my_id())
{
    Item::delete($item);
    flash("Report deleted.");
}
else
{
    flash("You can only delete your own reports.", "error");
}

redirect("dashboard.php");
