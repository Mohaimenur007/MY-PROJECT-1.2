<?php

// Everything about reports (lost and found items): saving, searching,
// photos, statistics and the smart matching score.

class Item
{
    // One report with its reporter's details.
    public static function find($id)
    {
        return Database::one(
            "SELECT items.*, users.Username, users.Email, users.Phone, users.IsBanned, users.ShowContact
             FROM items JOIN users ON items.UserID = users.ID
             WHERE items.ID = ?", "i", [$id]);
    }

    public static function byUser($user_id)
    {
        return Database::all("SELECT * FROM items WHERE UserID=? ORDER BY CreatedAt DESC, ID DESC", "i", [$user_id]);
    }

    public static function activeByUser($user_id)
    {
        return Database::all(
            "SELECT items.*, users.Username FROM items JOIN users ON items.UserID = users.ID
             WHERE items.UserID=? AND items.Status='Active'
             ORDER BY items.CreatedAt DESC, items.ID DESC", "i", [$user_id]);
    }

    public static function recent($limit)
    {
        return Database::all(
            "SELECT items.*, users.Username FROM items JOIN users ON items.UserID = users.ID
             WHERE items.Status='Active' AND users.IsBanned=0
             ORDER BY items.CreatedAt DESC, items.ID DESC LIMIT ?", "i", [$limit]);
    }

    // Resolved reports, newest first (the Reunited wall).
    public static function reunited($limit)
    {
        return Database::all(
            "SELECT items.*, users.Username FROM items JOIN users ON items.UserID = users.ID
             WHERE items.Status='Resolved' AND users.IsBanned=0
             ORDER BY items.ResolvedAt DESC, items.ID DESC LIMIT ?", "i", [$limit]);
    }

    // Browse page. $f: search, type, division, category, status, sort.
    // Returns ["items" => this page, "total" => all matching rows].
    public static function search($f, $page, $per_page)
    {
        $where = " FROM items JOIN users ON items.UserID = users.ID WHERE users.IsBanned = 0";
        $params = [];
        $types = "";

        if($f["status"] == "Active" || $f["status"] == "Resolved")
        {
            $where .= " AND items.Status = ?";
            $params[] = $f["status"];
            $types .= "s";
        }

        if($f["search"] != "")
        {
            $where .= " AND (items.ItemName LIKE ? OR items.Description LIKE ? OR items.Category LIKE ? OR items.District LIKE ? OR items.Division LIKE ?)";
            $like = "%" . $f["search"] . "%";
            array_push($params, $like, $like, $like, $like, $like);
            $types .= "sssss";
        }

        foreach(["type" => "items.Type", "division" => "items.Division", "category" => "items.Category"] as $key => $column)
        {
            if($f[$key] != "")
            {
                $where .= " AND " . $column . " = ?";
                $params[] = $f[$key];
                $types .= "s";
            }
        }

        $total = (int)Database::value("SELECT COUNT(*)" . $where, $types, $params);

        // The sort order comes from this fixed list, never from what the user typed.
        $orders = [
            "newest" => "items.CreatedAt DESC, items.ID DESC",
            "oldest" => "items.CreatedAt ASC, items.ID ASC",
            "date" => "items.EventDate DESC, items.ID DESC",
            "popular" => "items.Views DESC, items.ID DESC"
        ];
        $order = isset($orders[$f["sort"]]) ? $orders[$f["sort"]] : $orders["newest"];

        $params[] = $per_page;
        $params[] = (max(1, $page) - 1) * $per_page;
        $types .= "ii";

        $items = Database::all("SELECT items.*, users.Username" . $where . " ORDER BY " . $order . " LIMIT ? OFFSET ?", $types, $params);

        return ["items" => $items, "total" => $total];
    }

    public static function create($user_id, $d)
    {
        return Database::insert(
            "INSERT INTO items (UserID, Type, ItemName, Category, Description, Division, District, EventDate, Reward, Status, Views, CreatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,'Active',0,?)",
            "isssssssis",
            [$user_id, $d["type"], $d["item_name"], $d["category"], $d["description"], $d["division"], $d["district"], $d["event_date"], $d["reward"], now()]);
    }

    public static function update($id, $d)
    {
        Database::run(
            "UPDATE items SET Type=?, ItemName=?, Category=?, Description=?, Division=?, District=?, EventDate=?, Reward=? WHERE ID=?",
            "sssssssii",
            [$d["type"], $d["item_name"], $d["category"], $d["description"], $d["division"], $d["district"], $d["event_date"], $d["reward"], $id]);
    }

    public static function resolve($id, $note = null)
    {
        Database::run("UPDATE items SET Status='Resolved', ResolvedAt=?, ResolvedNote=? WHERE ID=?", "ssi", [now(), $note, $id]);
    }

    public static function reopen($id)
    {
        Database::run("UPDATE items SET Status='Active', ResolvedAt=NULL, ResolvedNote=NULL WHERE ID=?", "i", [$id]);
    }

    public static function addView($id)
    {
        Database::run("UPDATE items SET Views = Views + 1 WHERE ID=?", "i", [$id]);
    }

    // Removes a report, everything attached to it, and its photo files.
    public static function delete($item)
    {
        $files = self::photos($item);

        foreach(["item_images", "claims", "comments", "messages", "flags", "saved_items"] as $table)
        {
            Database::run("DELETE FROM " . $table . " WHERE ItemID=?", "i", [$item["ID"]]);
        }

        Database::run("DELETE FROM items WHERE ID=?", "i", [$item["ID"]]);

        foreach($files as $file)
        {
            Upload::delete($file);
        }
    }

    // ---- photos: the cover is items.ImagePath, the rest are rows in item_images ----

    public static function photos($item)
    {
        $paths = $item["ImagePath"] ? [$item["ImagePath"]] : [];

        foreach(Database::all("SELECT Path FROM item_images WHERE ItemID=? ORDER BY ID ASC", "i", [$item["ID"]]) as $row)
        {
            $paths[] = $row["Path"];
        }

        return $paths;
    }

    // Replaces the photo list. The first path becomes the cover.
    public static function setPhotos($id, $paths)
    {
        $paths = array_values($paths);

        Database::run("UPDATE items SET ImagePath=? WHERE ID=?", "si", [count($paths) > 0 ? $paths[0] : null, $id]);
        Database::run("DELETE FROM item_images WHERE ItemID=?", "i", [$id]);

        for($i = 1; $i < count($paths); $i++)
        {
            Database::run("INSERT INTO item_images (ItemID, Path) VALUES (?,?)", "is", [$id, $paths[$i]]);
        }
    }

    // ---- statistics ----

    public static function publicCount($resolved_only = false)
    {
        return (int)Database::value(
            "SELECT COUNT(*) FROM items JOIN users ON items.UserID = users.ID WHERE users.IsBanned = 0"
            . ($resolved_only ? " AND items.Status = 'Resolved'" : ""));
    }

    public static function count($where = "")
    {
        $conditions = ["" => "", "active" => " WHERE Status='Active'", "resolved" => " WHERE Status='Resolved'", "lost" => " WHERE Type='Lost'"];
        return (int)Database::value("SELECT COUNT(*) FROM items" . $conditions[$where]);
    }

    // Active reports per category or division: ["Keys" => 3, ...]
    public static function activeCounts($column)
    {
        $column = $column == "Division" ? "Division" : "Category";
        $counts = [];

        foreach(Database::all(
            "SELECT items." . $column . " AS Label, COUNT(*) AS Total FROM items JOIN users ON items.UserID = users.ID
             WHERE items.Status='Active' AND users.IsBanned=0 GROUP BY items." . $column) as $row)
        {
            $counts[$row["Label"]] = (int)$row["Total"];
        }

        return $counts;
    }

    public static function countBy($column)
    {
        $column = $column == "Division" ? "Division" : "Category";
        return Database::all("SELECT " . $column . " AS Label, COUNT(*) AS Total FROM items GROUP BY " . $column . " ORDER BY Total DESC");
    }

    // Reports posted on each of the last 7 days: ["2026-11-20" => 3, ...]
    public static function lastSevenDays()
    {
        $days = [];

        for($i = 6; $i >= 0; $i--)
        {
            $days[date("Y-m-d", strtotime("-" . $i . " days"))] = 0;
        }

        foreach(Database::all("SELECT CreatedAt FROM items WHERE CreatedAt >= ?", "s", [date("Y-m-d 00:00:00", strtotime("-6 days"))]) as $row)
        {
            $day = date("Y-m-d", strtotime($row["CreatedAt"]));
            if(isset($days[$day])) $days[$day]++;
        }

        return $days;
    }

    public static function adminSearch($q)
    {
        $like = "%" . $q . "%";

        return Database::all(
            "SELECT items.*, users.Username FROM items JOIN users ON items.UserID = users.ID
             WHERE items.ItemName LIKE ? OR users.Username LIKE ?
             ORDER BY items.ID DESC LIMIT 100", "ss", [$like, $like]);
    }

    public static function all()
    {
        return Database::all("SELECT items.*, users.Username FROM items JOIN users ON items.UserID = users.ID ORDER BY items.ID ASC");
    }

    // ---- smart matching ----

    // For a Lost report, look through active Found reports (and the other way round)
    // and keep the ones likely to be the same item, best first.
    public static function matches($item, $limit = 6)
    {
        $opposite = $item["Type"] == "Lost" ? "Found" : "Lost";

        $candidates = Database::all(
            "SELECT items.*, users.Username FROM items JOIN users ON items.UserID = users.ID
             WHERE items.Type = ? AND items.Status = 'Active' AND items.UserID != ? AND items.ID != ?
               AND users.IsBanned = 0 AND (items.Category = ? OR items.Division = ?)
             ORDER BY items.CreatedAt DESC LIMIT 300",
            "siiss", [$opposite, $item["UserID"], $item["ID"], $item["Category"], $item["Division"]]);

        $matches = [];

        foreach($candidates as $candidate)
        {
            $candidate["Score"] = self::score($item, $candidate);

            if($candidate["Score"] >= config("match_threshold"))
            {
                $matches[] = $candidate;
            }
        }

        usort($matches, function ($a, $b) { return $b["Score"] - $a["Score"]; });

        return array_slice($matches, 0, $limit);
    }

    // Scores two reports from 0 to 100:
    //   same category 35 · same division 15 · same district 15
    //   dates that make sense together up to 15 · shared words up to 30
    public static function score($a, $b)
    {
        $score = 0;

        if($a["Category"] == $b["Category"]) $score += 35;

        if($a["Division"] == $b["Division"])
        {
            $score += 15;
            if(self::lower(trim($a["District"])) == self::lower(trim($b["District"]))) $score += 15;
        }

        // An item can only be found on or after the day it was lost.
        $lost = $a["Type"] == "Lost" ? $a : $b;
        $found = $a["Type"] == "Lost" ? $b : $a;
        $days = (strtotime($found["EventDate"]) - strtotime($lost["EventDate"])) / 86400;

        if($days >= 0 && $days <= 3) $score += 15;
        else if($days > 3 && $days <= 14) $score += 10;
        else if($days > 14 && $days <= 45) $score += 4;
        else if($days < -2) $score -= 15;

        $name_a = self::words($a["ItemName"]);
        $name_b = self::words($b["ItemName"]);

        if(count(array_intersect($name_a, $name_b)) > 0) $score += 20;

        $all_a = array_unique(array_merge($name_a, self::words($a["Description"])));
        $all_b = array_unique(array_merge($name_b, self::words($b["Description"])));

        $score += min(10, count(array_intersect($all_a, $all_b)) * 4);

        return max(0, min(100, $score));
    }

    private static function lower($text)
    {
        return function_exists("mb_strtolower") ? mb_strtolower((string)$text, "UTF-8") : strtolower((string)$text);
    }

    // Meaningful lowercase words of 3+ letters.
    private static function words($text)
    {
        $stop = ["the", "and", "with", "for", "from", "near", "was", "has", "have", "this", "that",
                 "lost", "found", "item", "its", "not", "but", "are", "had", "color", "colour"];

        $words = [];

        foreach(preg_split('/[^\p{L}\p{N}]+/u', self::lower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part)
        {
            if(text_length($part) >= 3 && !in_array($part, $stop))
            {
                $words[] = $part;
            }
        }

        return array_values(array_unique($words));
    }
}
