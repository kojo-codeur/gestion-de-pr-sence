<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Restrict access to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

if (!isset($_GET['user_id'])) {
    echo "<p>ID utilisateur manquant.</p>";
    exit;
}

$user_id = (int)$_GET['user_id'];

try {
    $stmt = $db->prepare("SELECT date, entry_time, exit_time, status FROM presences WHERE user_id = ? ORDER BY date DESC LIMIT 20");
    $stmt->execute([$user_id]);
    $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($presences)) {
        echo "<p>Aucune présence enregistrée pour cet utilisateur.</p>";
        exit;
    }
    
    echo '<table class="table">';
    echo '<thead><tr><th>Date</th><th>Heure d\'arrivée</th><th>Heure de départ</th><th>Statut</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($presences as $presence) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($presence['date']) . '</td>';
        echo '<td>' . htmlspecialchars($presence['entry_time'] ?: '-') . '</td>';
        echo '<td>' . htmlspecialchars($presence['exit_time'] ?: '-') . '</td>';
        echo '<td><span class="badge ' . ($presence['status'] === 'present' ? 'badge-success' : 'badge-danger') . '">';
        echo $presence['status'] === 'present' ? 'Présent' : 'Absent';
        echo '</span></td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    
} catch (PDOException $e) {
    echo "<p>Erreur lors de la récupération des données: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>