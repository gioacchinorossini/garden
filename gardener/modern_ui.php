<?php
$base_path = '../';
$page_title = "Gardener Studio V2";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['ui_mode'] = 'modern';
$_SESSION['active_role'] = 'gardener';
$_SESSION['user_name'] = 'Mary Gardener';

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
include '../includes/gardener_modern_ui.php';
include '../includes/footer.php';
?>
