<?php
session_start();

// Inclure les fichiers nécessaires
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $current_date = date('Y-m-d');
    
    try {
        // Vérifier si l'utilisateur a déjà un enregistrement de présence pour aujourd'hui
        $stmt = $db->prepare("SELECT id, entry_time, exit_time, status FROM presences WHERE user_id = ? AND date = ?");
        $stmt->execute([$user_id, $current_date]);
        $presence = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($presence) {
            // Cas 1: L'utilisateur a une entrée mais pas de sortie
            if (!empty($presence['entry_time']) && empty($presence['exit_time'])) {
                // Marquer comme absent car il n'a pas fait de sortie
                $stmt = $db->prepare("UPDATE presences SET status = 'absent' WHERE id = ?");
                $stmt->execute([$presence['id']]);
            }
            // Cas 2: L'utilisateur n'a ni entrée ni sortie
            else if (empty($presence['entry_time']) && empty($presence['exit_time'])) {
                // Marquer comme absent car il n'a fait ni entrée ni sortie
                $stmt = $db->prepare("UPDATE presences SET status = 'absent' WHERE id = ?");
                $stmt->execute([$presence['id']]);
            }
            // Cas 3: L'utilisateur a les deux (entrée et sortie) - ne rien faire
        } else {
            // Cas 4: Aucune entrée pour aujourd'hui - vérifier d'abord si un enregistrement existe déjà
            // Cette double vérification est importante pour éviter les doublons
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM presences WHERE user_id = ? AND date = ?");
            $stmt->execute([$user_id, $current_date]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Seulement créer un nouvel enregistrement si aucun n'existe
            if ($count == 0) {
                $stmt = $db->prepare("INSERT INTO presences (user_id, date, status) VALUES (?, ?, 'absent')");
                $stmt->execute([$user_id, $current_date]);
            }
        }
    } catch (PDOException $e) {
        // Log l'erreur sans afficher à l'utilisateur
        error_log("Erreur lors de la mise à jour de la présence: " . $e->getMessage());
    }
    
    // Détruire la session
    session_destroy();
}

// Rediriger vers la page de connexion
header("Location: index.php");
exit();
?>