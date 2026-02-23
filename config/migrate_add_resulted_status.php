<?php
/**
 * One-time migration: add resulted_status column to exams table.
 * Run once from browser: index.php?action=exams&sub=migrateResultedStatus (admin only)
 * Or run this file via CLI: php -r "require 'config/database.php'; require 'config/migrate_add_resulted_status.php';"
 */
require_once __DIR__ . '/database.php';
$database = new Database();
$conn = $database->getDbConnection();
try {
    $conn->exec("ALTER TABLE exams ADD COLUMN resulted_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER final_exam_percentage");
    echo "Migration OK: resulted_status column added to exams.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column resulted_status already exists. Skipped.\n";
    } else {
        throw $e;
    }
}
