<?php

// Report a lost or found item. With ?id=5 the same form edits an existing report.

require "includes/init.php";
require_login();

$id = is_post() ? post_id() : get_id();
$editing = $id > 0;
$item = null;

if($editing)
{
    $item = Item::find($id);

    if(!$item || $item["UserID"] != my_id())
    {
        flash("You can only edit your own reports.", "error");
        redirect("dashboard.php");
    }
}

$photos = $item ? Item::photos($item) : [];
$error = "";

// Values shown in the form.
if($item)
{
    $data = [
        "type" => $item["Type"], "item_name" => $item["ItemName"], "category" => $item["Category"],
        "description" => $item["Description"], "division" => $item["Division"], "district" => $item["District"],
        "event_date" => $item["EventDate"], "reward" => $item["Reward"]
    ];
}
else
{
    $data = [
        "type" => get("type") == "found" ? "Found" : "Lost", "item_name" => "", "category" => "",
        "description" => "", "division" => me()["Division"], "district" => me()["District"],
        "event_date" => date("Y-m-d"), "reward" => ""
    ];
}

if(is_post())
{
    check_csrf();

    $data = [
        "type" => post("type"),
        "item_name" => post("item_name"),
        "category" => post("category"),
        "description" => post("description"),
        "division" => post("division"),
        "district" => post("district"),
        "event_date" => post("event_date"),
        "reward" => post("reward")
    ];

    // Which existing photos to keep.
    $remove = isset($_POST["remove_photos"]) && is_array($_POST["remove_photos"]) ? array_map("intval", $_POST["remove_photos"]) : [];
    $keep = [];
    foreach($photos as $i => $path)
    {
        if(!in_array($i, $remove, true)) $keep[] = $path;
    }

    $date_ok = preg_match('/^\d{4}-\d{2}-\d{2}$/', $data["event_date"]) && strtotime($data["event_date"]) !== false;

    if(!in_array($data["type"], ["Lost", "Found"], true)) $error = "Choose whether you lost or found the item.";
    else if($data["item_name"] == "" || text_length($data["item_name"]) > 100) $error = "Enter the item name (up to 100 characters).";
    else if(!in_array($data["category"], $categories, true)) $error = "Select a valid category.";
    else if(!$date_ok) $error = "Select a date.";
    else if($data["event_date"] > date("Y-m-d")) $error = "The date can't be in the future.";
    else if(!in_array($data["division"], $divisions, true)) $error = "Select a valid division.";
    else if($data["district"] == "" || text_length($data["district"]) > 50) $error = "Enter a district or area.";
    else if(text_length($data["description"]) > 2000) $error = "The description can be up to 2000 characters.";
    else if($data["type"] == "Lost" && $data["reward"] != "" && (!preg_match('/^[0-9]{1,8}$/', $data["reward"]) || (int)$data["reward"] < 1 || (int)$data["reward"] > 10000000)) $error = "Enter a reward between ৳1 and ৳10,000,000, or leave it empty.";
    else if(count($keep) + Upload::countSent("photos") > config("max_photos")) $error = "You can have up to " . config("max_photos") . " photos on a report.";

    if($error == "")
    {
        list($new_photos, $error) = Upload::storeMany("photos");
    }

    if($error == "")
    {
        $data["reward"] = $data["type"] == "Lost" && $data["reward"] != "" ? (int)$data["reward"] : null;

        if($editing)
        {
            Item::update($id, $data);

            // Delete the photo files that were removed.
            foreach($photos as $path)
            {
                if(!in_array($path, $keep, true)) Upload::delete($path);
            }

            Item::setPhotos($id, array_merge($keep, $new_photos));
            flash("Report updated.");
            redirect("item_detail.php", ["id" => $id]);
        }

        $new_id = Item::create(my_id(), $data);
        Item::setPhotos($new_id, $new_photos);
        $new_item = Item::find($new_id);

        // Tell both sides about strong matches, and anyone whose saved search fits.
        $matches = Item::matches($new_item, 6);

        foreach($matches as $m)
        {
            Notification::send(my_id(), "match", "A " . strtolower($m["Type"]) . " report “" . $m["ItemName"] . "” may match your " . strtolower($new_item["Type"]) . " report “" . $new_item["ItemName"] . "”.", "item_detail.php?id=" . $m["ID"]);
            Notification::send($m["UserID"], "match", "A " . strtolower($new_item["Type"]) . " report “" . $new_item["ItemName"] . "” may match your " . strtolower($m["Type"]) . " report “" . $m["ItemName"] . "”.", "item_detail.php?id=" . $new_id);
        }

        SearchAlert::notifyFor($new_item);

        flash(count($matches) > 0
            ? "Notice posted. We found " . plural(count($matches), "possible match", "possible matches") . "!"
            : "Notice posted. You'll get an alert if a match turns up.");

        redirect("item_detail.php", ["id" => $new_id]);
    }
}

$page_title = $editing ? "Edit report" : "Report an item";
require "includes/header.php";
?>

<div class="container-xl page page-narrow">

    <?= page_hero($editing ? "Edit notice" : "New notice", $editing ? "Edit your report" : "Pin a new notice", "The more detail you give, the better Lostly can match it with other reports.") ?>

    <form method="post" action="report_item.php" enctype="multipart/form-data" class="panel js-validate">
        <?= csrf_field() ?>
        <?php if($editing) { ?><input type="hidden" name="id" value="<?= $id ?>"><?php } ?>

        <span class="form-label d-block">What happened?</span>
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <input type="radio" class="btn-check" name="type" id="type-lost" value="Lost"<?= $data["type"] == "Lost" ? " checked" : "" ?>>
                <label class="type-option type-lost" for="type-lost"><b>I lost it</b><small>Let others help you find it</small></label>
            </div>
            <div class="col-sm-6">
                <input type="radio" class="btn-check" name="type" id="type-found" value="Found"<?= $data["type"] == "Found" ? " checked" : "" ?>>
                <label class="type-option type-found" for="type-found"><b>I found it</b><small>Help return it to its owner</small></label>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <label for="item_name" class="form-label">Item name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" maxlength="100" placeholder="e.g. Black leather wallet" value="<?= e($data["item_name"]) ?>" data-required="Enter the item name">
            </div>
            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" data-required="Select a valid category"><?= options($categories, $data["category"], "Select Category") ?></select>
            </div>
            <div class="col-md-6">
                <label for="event_date" class="form-label">Date it happened</label>
                <input type="date" class="form-control" id="event_date" name="event_date" max="<?= date("Y-m-d") ?>" value="<?= e($data["event_date"]) ?>" data-required="Select a date">
            </div>
            <div class="col-md-6">
                <label for="division" class="form-label">Division</label>
                <select class="form-select" id="division" name="division" data-required="Select a valid division"><?= options($divisions, $data["division"], "Select Division") ?></select>
            </div>
            <div class="col-md-6">
                <label for="district" class="form-label">District / area</label>
                <input type="text" class="form-control" id="district" name="district" maxlength="50" list="district-list" value="<?= e($data["district"]) ?>" data-required="Enter a district" autocomplete="off">
                <?= district_datalist() ?>
            </div>
            <div class="col-md-6 js-reward-field"<?= $data["type"] == "Found" ? " hidden" : "" ?>>
                <label for="reward" class="form-label">Reward in taka (optional)</label>
                <div class="input-group">
                    <span class="input-group-text">৳</span>
                    <input type="number" class="form-control" id="reward" name="reward" min="1" max="10000000" step="1" inputmode="numeric" placeholder="e.g. 2000" value="<?= e($data["reward"]) ?>">
                </div>
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" maxlength="2000" placeholder="Colour, brand, marks, where exactly…"><?= e($data["description"]) ?></textarea>
            </div>
            <div class="col-12">
                <label for="photos" class="form-label">Photos (optional · up to <?= config("max_photos") ?> · JPG, PNG or WEBP · max 5MB each)</label>

                <?php if(count($photos) > 0) { ?>
                <div class="photo-manage">
                    <?php foreach($photos as $i => $path) { ?>
                    <label class="photo-manage-item">
                        <img src="<?= e($path) ?>" alt="Photo <?= $i + 1 ?>">
                        <?php if($i == 0) { ?><span class="photo-cover">Cover</span><?php } ?>
                        <span class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_photos[]" value="<?= $i ?>">
                            <span class="form-check-label">Remove</span>
                        </span>
                    </label>
                    <?php } ?>
                </div>
                <?php } ?>

                <div class="upload-drop">
                    <input type="file" class="form-control js-photos-input" id="photos" name="photos[]" multiple accept="image/png, image/jpeg, image/webp" data-max="<?= config("max_photos") ?>" data-preview="photo-previews">
                    <div id="photo-previews" class="photo-previews"></div>
                </div>
            </div>
        </div>

        <?php if($error != "") { ?>
        <div class="alert alert-lost form-message mt-3" role="alert"><?= e($error) ?></div>
        <?php } ?>

        <div class="d-flex flex-wrap justify-content-end gap-3 mt-4 form-actions">
            <a href="<?= $editing ? "item_detail.php?id=" . $id : "dashboard.php" ?>" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-ink"><?= $editing ? "Save changes" : "Post notice" ?></button>
        </div>
    </form>

</div>

<?php require "includes/footer.php"; ?>
