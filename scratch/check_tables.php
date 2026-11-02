<?php
require_once __DIR__ . '/../includes/db.php';
echo "=== REQUESTS TABLE ===\n";
foreach ($pdo->query("DESCRIBE `requests`")->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}
echo "\n=== PLOTS TABLE ===\n";
foreach ($pdo->query("DESCRIBE `plots`")->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "{$col['Field']} - {$col['Type']}\n";
}
