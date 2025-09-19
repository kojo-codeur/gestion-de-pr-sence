<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

try {
    // Vérifier si l'utilisateur a une entrée mais pas de sortie pour aujourd'hui
    $stmt = $db->prepare("SELECT id FROM presences WHERE user_id = ? AND date = ? AND entry_time IS NOT NULL AND exit_time IS NULL");
    $stmt->execute([$user_id, $current_date]);
    $presence = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($presence) {
        // Marquer comme absent
        $stmt = $db->prepare("UPDATE presences SET status = 'absent' WHERE id = ?");
        $stmt->execute([$presence['id']]);
    }
} catch (PDOException $e) {
    // Log l'erreur sans afficher à l'utilisateur
    error_log("Erreur mark_absence: " . $e->getMessage());
}

exit;