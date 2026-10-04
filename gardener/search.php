<?php
// Route gardener search.php to the dashboard page
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: dashboard.php" . $qs);
exit;