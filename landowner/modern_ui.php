<?php
$base_path = '../';
$page_title = "Landowner Studio V2";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['ui_mode'] = 'modern';
$_SESSION['active_role'] = 'landowner';
$_SESSION['user_name'] = 'John Landowner';

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
include '../includes/landowner_modern_ui.php';
include '../includes/footer.php';
?>
