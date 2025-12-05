<?php
session_start();
error_reporting(E_ALL);

// Use your existing database connection
include('../includes/config.php');   // must define $dbh (PDO)

if (!$dbh) {
    die("Database connection not available.");
}

echo "Connected Successfully<br><br>";

$sqlList = [

    // 1) Make id the PRIMARY KEY
    "ALTER TABLE exam
        DROP PRIMARY KEY,
        ADD PRIMARY KEY (id)",

    // 2) Auto increment for id
    "ALTER TABLE exam
        MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT",

    // 3) Convert `time` to TIME
    "ALTER TABLE exam
        MODIFY COLUMN `time` TIME NOT NULL",

    // 4) Convert `date` to DATE NULL
    "ALTER TABLE exam
        MODIFY COLUMN `date` DATE NULL",

    // 5) Unique mid + bid
    "ALTER TABLE exam
        ADD CONSTRAINT uniq_mid_bid UNIQUE (mid, bid)",

    // 6) Status default value
    "ALTER TABLE exam
        MODIFY COLUMN `Status` VARCHAR(20) NOT NULL DEFAULT 'Pending'"
];

foreach ($sqlList as $index => $query) {
    try {
        $dbh->exec($query);
        echo "Query " . ($index + 1) . " executed successfully.<br>";
    } catch (Exception $e) {
        echo "❌ Error in Query " . ($index + 1) . ": " . $e->getMessage() . "<br>";
    }
}

echo "<br>✔ All SQL commands processed.";
?>
