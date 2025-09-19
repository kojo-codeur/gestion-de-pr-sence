<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

$database = new Database();
$db = $database->getConnection();

// Vérifier si l'utilisateur est connecté pour les pages protégées
$protected_pages = ['dashboard.php', 'admin.php', 'scan.php', 'generate_qr.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $protected_pages) && !isLoggedIn()) {
    redirect('index.php');
}

// Vérifier les privilèges admin pour la page d'administration
if ($current_page === 'admin.php' && !isAdmin()) {
    redirect('dashboard.php');
}

// Traitement de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.php');
}
?>