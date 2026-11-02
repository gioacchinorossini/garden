<?php
require_once __DIR__ . '/../includes/db.php';

try {
    // 1. Expand requests status enum to include 'counter_offered'
    $pdo->exec("
        ALTER TABLE `requests` 
        MODIFY COLUMN `status` ENUM('pending', 'offer_sent', 'counter_offered', 'approved', 'rejected', 'declined') NOT NULL DEFAULT 'pending'
    ");
    echo "Expanded requests status enum.\n";

    // 2. Add counter_rental_rate, counter_notes, counter_at if not present
    $cols = $pdo->query("DESCRIBE `requests`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('counter_rental_rate', $cols)) {
        $pdo->exec("ALTER TABLE `requests` ADD COLUMN `counter_rental_rate` DECIMAL(10,2) DEFAULT NULL AFTER `offered_rental_rate`");
        echo "Added counter_rental_rate to requests.\n";
    }
    if (!in_array('counter_notes', $cols)) {
        $pdo->exec("ALTER TABLE `requests` ADD COLUMN `counter_notes` TEXT DEFAULT NULL AFTER `counter_rental_rate`");
        echo "Added counter_notes to requests.\n";
    }
    if (!in_array('counter_at', $cols)) {
        $pdo->exec("ALTER TABLE `requests` ADD COLUMN `counter_at` TIMESTAMP NULL DEFAULT NULL AFTER `counter_notes`");
        echo "Added counter_at to requests.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
