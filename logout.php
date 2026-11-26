<?php

// Logs out (POST only, so a link on another site can't log you out).

require "includes/init.php";

if(is_post())
{
    check_csrf();
    $_SESSION = [];
    session_regenerate_id(true);
    flash("You have been logged out.");
}

redirect("index.php");
