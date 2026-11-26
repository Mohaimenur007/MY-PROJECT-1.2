<?php

// Database helpers for everything except reports (those are in ItemModel.php).

function now()
{
    return date("Y-m-d H:i:s");
}

class User
{
    public static function find($id)
    {
        return Database::one("SELECT * FROM users WHERE ID=?", "i", [$id]);
    }

    public static function findByUsername($username)
    {
        return Database::one("SELECT * FROM users WHERE LOWER(Username) = LOWER(?)", "s", [$username]);
    }

    public static function usernameTaken($username, $except_id = 0)
    {
        return Database::value("SELECT COUNT(*) FROM users WHERE LOWER(Username) = LOWER(?) AND ID != ?", "si", [$username, $except_id]) > 0;
    }

    public static function emailTaken($email, $except_id = 0)
    {
        return Database::value("SELECT COUNT(*) FROM users WHERE LOWER(Email) = LOWER(?) AND ID != ?", "si", [$email, $except_id]) > 0;
    }

    public static function create($data)
    {
        return Database::insert(
            "INSERT INTO users (Username, Email, Phone, Division, District, Password, Role, IsBanned, ShowContact, CreatedAt) VALUES (?,?,?,?,?,?,'user',0,1,?)",
            "sssssss",
            [$data["username"], $data["email"], $data["phone"], $data["division"], $data["district"],
             password_hash($data["password"], PASSWORD_DEFAULT), now()]);
    }

    public static function update($id, $data)
    {
        Database::run(
            "UPDATE users SET Username=?, Email=?, Phone=?, Division=?, District=?, ShowContact=? WHERE ID=?",
            "sssssii",
            [$data["username"], $data["email"], $data["phone"], $data["division"], $data["district"], $data["show_contact"], $id]);
    }

    public static function setPassword($id, $password)
    {
        Database::run("UPDATE users SET Password=? WHERE ID=?", "si", [password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public static function all()
    {
        return Database::all(
            "SELECT users.*, (SELECT COUNT(*) FROM items WHERE items.UserID = users.ID) AS ReportCount
             FROM users ORDER BY users.ID ASC");
    }

    public static function count($active_only = false)
    {
        return (int)Database::value("SELECT COUNT(*) FROM users" . ($active_only ? " WHERE IsBanned = 0" : ""));
    }

    public static function setBanned($id, $banned)
    {
        Database::run("UPDATE users SET IsBanned=? WHERE ID=?", "ii", [$banned ? 1 : 0, $id]);
    }

    public static function setRole($id, $role)
    {
        Database::run("UPDATE users SET Role=? WHERE ID=?", "si", [$role == "admin" ? "admin" : "user", $id]);
    }

    public static function admins()
    {
        return Database::all("SELECT ID FROM users WHERE Role='admin' AND IsBanned=0");
    }

    // A few members for the avatar row on the home page.
    public static function sample($limit)
    {
        return Database::all("SELECT ID, Username FROM users WHERE IsBanned=0 ORDER BY ID ASC LIMIT ?", "i", [$limit]);
    }
}

class Claim
{
    public static function create($item_id, $user_id, $message)
    {
        return Database::insert("INSERT INTO claims (ItemID, ClaimantID, Message, Status, CreatedAt) VALUES (?,?,?,'Pending',?)",
            "iiss", [$item_id, $user_id, $message, now()]);
    }

    public static function find($id)
    {
        return Database::one("SELECT * FROM claims WHERE ID=?", "i", [$id]);
    }

    public static function forItem($item_id)
    {
        return Database::all(
            "SELECT claims.*, users.Username FROM claims JOIN users ON claims.ClaimantID = users.ID
             WHERE claims.ItemID=? ORDER BY claims.CreatedAt DESC, claims.ID DESC", "i", [$item_id]);
    }

    public static function mine($item_id, $user_id)
    {
        return Database::one("SELECT * FROM claims WHERE ItemID=? AND ClaimantID=?", "ii", [$item_id, $user_id]);
    }

    public static function setStatus($id, $status)
    {
        Database::run("UPDATE claims SET Status=? WHERE ID=?", "si", [$status, $id]);
    }

    // Claims other people sent on my reports that still need an answer.
    public static function incoming($owner_id)
    {
        return Database::all(
            "SELECT claims.*, users.Username, items.ItemName, items.Type
             FROM claims
             JOIN items ON claims.ItemID = items.ID
             JOIN users ON claims.ClaimantID = users.ID
             WHERE items.UserID=? AND claims.Status='Pending'
             ORDER BY claims.CreatedAt DESC", "i", [$owner_id]);
    }

    public static function sentBy($user_id)
    {
        return Database::all(
            "SELECT claims.*, items.ItemName FROM claims JOIN items ON claims.ItemID = items.ID
             WHERE claims.ClaimantID=? ORDER BY claims.CreatedAt DESC", "i", [$user_id]);
    }

    public static function pendingFor($item_id)
    {
        return Database::all("SELECT * FROM claims WHERE ItemID=? AND Status='Pending'", "i", [$item_id]);
    }

    public static function count()
    {
        return (int)Database::value("SELECT COUNT(*) FROM claims");
    }
}

class Comment
{
    public static function create($item_id, $user_id, $body)
    {
        return Database::insert("INSERT INTO comments (ItemID, UserID, Body, CreatedAt) VALUES (?,?,?,?)",
            "iiss", [$item_id, $user_id, $body, now()]);
    }

    public static function find($id)
    {
        return Database::one("SELECT * FROM comments WHERE ID=?", "i", [$id]);
    }

    public static function forItem($item_id)
    {
        return Database::all(
            "SELECT comments.*, users.Username FROM comments JOIN users ON comments.UserID = users.ID
             WHERE comments.ItemID=? ORDER BY comments.CreatedAt ASC, comments.ID ASC", "i", [$item_id]);
    }

    public static function delete($id)
    {
        Database::run("DELETE FROM comments WHERE ID=?", "i", [$id]);
    }

    public static function countBy($user_id)
    {
        return (int)Database::value("SELECT COUNT(*) FROM comments WHERE UserID=?", "i", [$user_id]);
    }
}

class Message
{
    public static function send($item_id, $from, $to, $body)
    {
        return Database::insert("INSERT INTO messages (ItemID, SenderID, ReceiverID, Body, IsRead, CreatedAt) VALUES (?,?,?,?,0,?)",
            "iiiss", [$item_id, $from, $to, $body, now()]);
    }

    // Messages between $me and $other about one report, newer than message $after.
    public static function thread($item_id, $me, $other, $after = 0)
    {
        return Database::all(
            "SELECT ID, SenderID, Body, CreatedAt FROM messages
             WHERE ItemID=? AND ID > ?
               AND ((SenderID=? AND ReceiverID=?) OR (SenderID=? AND ReceiverID=?))
             ORDER BY ID ASC",
            "iiiiii", [$item_id, $after, $me, $other, $other, $me]);
    }

    public static function markRead($item_id, $me, $other)
    {
        Database::run("UPDATE messages SET IsRead=1 WHERE ItemID=? AND SenderID=? AND ReceiverID=? AND IsRead=0",
            "iii", [$item_id, $other, $me]);
    }

    public static function unreadCount($user_id)
    {
        return (int)Database::value("SELECT COUNT(*) FROM messages WHERE ReceiverID=? AND IsRead=0", "i", [$user_id]);
    }

    // Inbox: one entry per conversation with its newest message.
    public static function inbox($user_id)
    {
        $rows = Database::all(
            "SELECT messages.*, items.ItemName, items.Type,
                    sender.Username AS SenderName, receiver.Username AS ReceiverName
             FROM messages
             JOIN items ON messages.ItemID = items.ID
             JOIN users sender ON messages.SenderID = sender.ID
             JOIN users receiver ON messages.ReceiverID = receiver.ID
             WHERE messages.SenderID = ? OR messages.ReceiverID = ?
             ORDER BY messages.ID DESC
             LIMIT 500", "ii", [$user_id, $user_id]);

        $threads = [];

        foreach($rows as $row)
        {
            $mine = $row["SenderID"] == $user_id;
            $other_id = $mine ? $row["ReceiverID"] : $row["SenderID"];
            $key = $row["ItemID"] . "-" . $other_id;

            if(!isset($threads[$key]))
            {
                $threads[$key] = [
                    "item_id" => $row["ItemID"], "item_name" => $row["ItemName"], "type" => $row["Type"],
                    "other_id" => $other_id, "other_name" => $mine ? $row["ReceiverName"] : $row["SenderName"],
                    "last_body" => $row["Body"], "last_mine" => $mine, "last_time" => $row["CreatedAt"], "unread" => 0
                ];
            }

            if(!$mine && !$row["IsRead"])
            {
                $threads[$key]["unread"]++;
            }
        }

        return array_values($threads);
    }

    public static function count()
    {
        return (int)Database::value("SELECT COUNT(*) FROM messages");
    }
}

class Notification
{
    public static function send($user_id, $type, $body, $link)
    {
        Database::insert("INSERT INTO notifications (UserID, Type, Body, Link, IsRead, CreatedAt) VALUES (?,?,?,?,0,?)",
            "issss", [$user_id, $type, $body, $link, now()]);
    }

    public static function forUser($user_id)
    {
        return Database::all("SELECT * FROM notifications WHERE UserID=? ORDER BY CreatedAt DESC, ID DESC LIMIT 100", "i", [$user_id]);
    }

    public static function unreadCount($user_id)
    {
        return (int)Database::value("SELECT COUNT(*) FROM notifications WHERE UserID=? AND IsRead=0", "i", [$user_id]);
    }

    public static function markAllRead($user_id)
    {
        Database::run("UPDATE notifications SET IsRead=1 WHERE UserID=? AND IsRead=0", "i", [$user_id]);
    }

    public static function clear($user_id)
    {
        Database::run("DELETE FROM notifications WHERE UserID=?", "i", [$user_id]);
    }
}

class Saved
{
    public static function has($user_id, $item_id)
    {
        return Database::value("SELECT COUNT(*) FROM saved_items WHERE UserID=? AND ItemID=?", "ii", [$user_id, $item_id]) > 0;
    }

    public static function toggle($user_id, $item_id)
    {
        if(self::has($user_id, $item_id))
        {
            Database::run("DELETE FROM saved_items WHERE UserID=? AND ItemID=?", "ii", [$user_id, $item_id]);
            return false;
        }

        Database::run("INSERT INTO saved_items (UserID, ItemID, CreatedAt) VALUES (?,?,?)", "iis", [$user_id, $item_id, now()]);
        return true;
    }

    public static function itemsFor($user_id)
    {
        return Database::all(
            "SELECT items.*, users.Username FROM saved_items
             JOIN items ON saved_items.ItemID = items.ID
             JOIN users ON items.UserID = users.ID
             WHERE saved_items.UserID=? AND users.IsBanned=0
             ORDER BY saved_items.CreatedAt DESC", "i", [$user_id]);
    }

    public static function watchers($item_id)
    {
        return Database::all("SELECT UserID FROM saved_items WHERE ItemID=?", "i", [$item_id]);
    }
}

class SearchAlert
{
    public static function forUser($user_id)
    {
        return Database::all("SELECT * FROM search_alerts WHERE UserID=? ORDER BY ID DESC", "i", [$user_id]);
    }

    public static function create($user_id, $s)
    {
        Database::insert("INSERT INTO search_alerts (UserID, Keyword, Type, Category, Division, CreatedAt) VALUES (?,?,?,?,?,?)",
            "isssss", [$user_id, $s["keyword"], $s["type"], $s["category"], $s["division"], now()]);
    }

    public static function exists($user_id, $s)
    {
        return Database::value(
            "SELECT COUNT(*) FROM search_alerts WHERE UserID=? AND Keyword=? AND Type=? AND Category=? AND Division=?",
            "issss", [$user_id, $s["keyword"], $s["type"], $s["category"], $s["division"]]) > 0;
    }

    public static function delete($id, $user_id)
    {
        Database::run("DELETE FROM search_alerts WHERE ID=? AND UserID=?", "ii", [$id, $user_id]);
    }

    public static function describe($s)
    {
        $parts = [];
        if($s["Keyword"] != "") $parts[] = "“" . $s["Keyword"] . "”";
        if($s["Type"] != "") $parts[] = $s["Type"];
        if($s["Category"] != "") $parts[] = $s["Category"];
        if($s["Division"] != "") $parts[] = $s["Division"];
        return count($parts) > 0 ? implode(" · ", $parts) : "Any new report";
    }

    // Alerts everyone whose saved search fits a newly posted report.
    public static function notifyFor($item)
    {
        $told = [];

        foreach(Database::all("SELECT * FROM search_alerts WHERE UserID != ?", "i", [$item["UserID"]]) as $s)
        {
            if(isset($told[$s["UserID"]])) continue;
            if($s["Type"] != "" && $s["Type"] != $item["Type"]) continue;
            if($s["Category"] != "" && $s["Category"] != $item["Category"]) continue;
            if($s["Division"] != "" && $s["Division"] != $item["Division"]) continue;

            if($s["Keyword"] != "")
            {
                $haystack = strtolower($item["ItemName"] . " " . $item["Description"] . " " . $item["Category"] . " " . $item["District"] . " " . $item["Division"]);
                if(strpos($haystack, strtolower($s["Keyword"])) === false) continue;
            }

            $told[$s["UserID"]] = true;
            Notification::send($s["UserID"], "search", "New report for your saved search " . self::describe($s) . ": “" . $item["ItemName"] . "”.", "item_detail.php?id=" . $item["ID"]);
        }
    }
}

class Flag
{
    public static function create($item_id, $user_id, $reason)
    {
        Database::insert("INSERT INTO flags (ItemID, UserID, Reason, CreatedAt) VALUES (?,?,?,?)", "iiss", [$item_id, $user_id, $reason, now()]);
    }

    public static function exists($item_id, $user_id)
    {
        return Database::value("SELECT COUNT(*) FROM flags WHERE ItemID=? AND UserID=?", "ii", [$item_id, $user_id]) > 0;
    }

    public static function open()
    {
        return Database::all(
            "SELECT flags.ID AS FlagID, flags.Reason, flags.CreatedAt, flags.ItemID,
                    items.ItemName, owner.Username AS OwnerName, reporter.Username AS ReporterName
             FROM flags
             JOIN items ON flags.ItemID = items.ID
             JOIN users owner ON items.UserID = owner.ID
             JOIN users reporter ON flags.UserID = reporter.ID
             ORDER BY flags.CreatedAt DESC");
    }

    public static function find($id)
    {
        return Database::one("SELECT flags.*, items.ItemName FROM flags JOIN items ON flags.ItemID = items.ID WHERE flags.ID=?", "i", [$id]);
    }

    public static function delete($id)
    {
        Database::run("DELETE FROM flags WHERE ID=?", "i", [$id]);
    }

    public static function count()
    {
        return (int)Database::value("SELECT COUNT(*) FROM flags");
    }
}

class ContactMessage
{
    public static function create($name, $email, $subject, $body)
    {
        Database::insert("INSERT INTO contact_messages (Name, Email, Subject, Body, IsRead, CreatedAt) VALUES (?,?,?,?,0,?)",
            "sssss", [$name, $email, $subject, $body, now()]);
    }

    public static function all()
    {
        return Database::all("SELECT * FROM contact_messages ORDER BY CreatedAt DESC, ID DESC");
    }

    public static function unreadCount()
    {
        return (int)Database::value("SELECT COUNT(*) FROM contact_messages WHERE IsRead=0");
    }

    public static function markAllRead()
    {
        Database::run("UPDATE contact_messages SET IsRead=1 WHERE IsRead=0");
    }

    public static function delete($id)
    {
        Database::run("DELETE FROM contact_messages WHERE ID=?", "i", [$id]);
    }
}

class AdminLog
{
    public static function add($admin_id, $action, $detail)
    {
        Database::insert("INSERT INTO admin_log (UserID, Action, Detail, CreatedAt) VALUES (?,?,?,?)", "isss", [$admin_id, $action, $detail, now()]);
    }

    public static function recent()
    {
        return Database::all(
            "SELECT admin_log.*, users.Username FROM admin_log LEFT JOIN users ON admin_log.UserID = users.ID
             ORDER BY admin_log.CreatedAt DESC, admin_log.ID DESC LIMIT 200");
    }
}

class Setting
{
    public static function get($name)
    {
        $value = Database::value("SELECT Value FROM settings WHERE Name=?", "s", [$name]);
        return $value === null ? "" : $value;
    }

    public static function set($name, $value)
    {
        Database::run("DELETE FROM settings WHERE Name=?", "s", [$name]);
        Database::run("INSERT INTO settings (Name, Value) VALUES (?,?)", "ss", [$name, $value]);
    }
}

// Stores and removes report photos in uploads/items/.
class Upload
{
    // How many files were chosen in a "multiple" file field.
    public static function countSent($field)
    {
        $count = 0;

        if(isset($_FILES[$field]["error"]) && is_array($_FILES[$field]["error"]))
        {
            foreach($_FILES[$field]["error"] as $error)
            {
                if($error != UPLOAD_ERR_NO_FILE) $count++;
            }
        }

        return $count;
    }

    // Stores every photo in name="photos[]". Returns [paths, error]. If one file is bad, none are kept.
    public static function storeMany($field)
    {
        $paths = [];

        if(!isset($_FILES[$field]["error"]) || !is_array($_FILES[$field]["error"]))
        {
            return [$paths, ""];
        }

        $files = $_FILES[$field];

        foreach($files["error"] as $i => $error)
        {
            if($error == UPLOAD_ERR_NO_FILE) continue;

            list($path, $message) = self::storeOne($error, $files["size"][$i], $files["tmp_name"][$i]);

            if($message != "")
            {
                foreach($paths as $done) self::delete($done);
                return [[], $message];
            }

            $paths[] = $path;
        }

        return [$paths, ""];
    }

    private static function storeOne($error, $size, $tmp_name)
    {
        if($error != UPLOAD_ERR_OK)
        {
            return [null, "Image upload failed, please try again."];
        }

        if($size > config("max_upload_bytes"))
        {
            return [null, "Each image must be smaller than 5MB."];
        }

        // Trust the file's real contents, never the name the browser sent.
        $info = @getimagesize($tmp_name);
        $allowed = [IMAGETYPE_JPEG => "jpg", IMAGETYPE_PNG => "png", IMAGETYPE_WEBP => "webp"];

        if(!$info || !isset($allowed[$info[2]]))
        {
            return [null, "Photos must be real JPG, PNG or WEBP images."];
        }

        $folder = ROOT . "/uploads/items/";

        if(!is_dir($folder))
        {
            mkdir($folder, 0755, true);
        }

        $name = "item_" . bin2hex(random_bytes(12)) . "." . $allowed[$info[2]];

        if(!move_uploaded_file($tmp_name, $folder . $name))
        {
            return [null, "Image upload failed, please try again."];
        }

        return ["uploads/items/" . $name, ""];
    }

    public static function delete($path)
    {
        // Only ever remove files inside uploads/items/.
        if($path && strpos($path, "uploads/items/") === 0 && strpos($path, "..") === false)
        {
            $full = ROOT . "/" . $path;
            if(is_file($full)) unlink($full);
        }
    }
}
