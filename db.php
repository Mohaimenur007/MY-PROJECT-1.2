<?php

// ---------------------------------------------------------------------
// Database connection. Change these four values if your MySQL setup
// is different (XAMPP's default is user "root" with no password).
// ---------------------------------------------------------------------

$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "lostly";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect($db_host, $db_user, $db_password, $db_name);

if(!$conn)
{
    http_response_code(500);
    die("Could not connect to the database. Check that MySQL is running and that the settings in db.php are correct.");
}

mysqli_set_charset($conn, "utf8mb4");

// Keep MySQL on Bangladesh time, the same clock PHP uses.
mysqli_query($conn, "SET time_zone = '+06:00'");


// Small helper around mysqli. Every query is a prepared statement,
// so user input can never change the SQL.
//   $types is one letter per value: "i" for numbers, "s" for text.
class Database
{
    public static function run($sql, $types = "", $params = [])
    {
        global $conn;

        $stmt = mysqli_prepare($conn, $sql);

        if(!$stmt)
        {
            error_log("Lostly SQL error: " . mysqli_error($conn) . " in: " . $sql);
            return false;
        }

        if($types !== "")
        {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        mysqli_stmt_execute($stmt);
        return $stmt;
    }

    // Every row as an array.
    public static function all($sql, $types = "", $params = [])
    {
        $rows = [];
        $stmt = self::run($sql, $types, $params);

        if($stmt)
        {
            $result = mysqli_stmt_get_result($stmt);

            while($result && $row = mysqli_fetch_assoc($result))
            {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    // The first row, or null.
    public static function one($sql, $types = "", $params = [])
    {
        $rows = self::all($sql, $types, $params);
        return count($rows) > 0 ? $rows[0] : null;
    }

    // The first value of the first row (handy for COUNT(*)).
    public static function value($sql, $types = "", $params = [])
    {
        $row = self::one($sql, $types, $params);
        return $row ? array_values($row)[0] : null;
    }

    // Runs an INSERT and returns the new row's ID.
    public static function insert($sql, $types = "", $params = [])
    {
        global $conn;

        $stmt = self::run($sql, $types, $params);
        return $stmt ? mysqli_insert_id($conn) : 0;
    }
}
