<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Restrict access to authenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle presence actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'mark_presence') {
    try {
        $user_id = $_SESSION['user_id'];
        $type = $_POST['type'];
        $current_time = date('H:i:s');
        $current_date = date('Y-m-d');

        // Check if entry exists for today
        $stmt = $db->prepare("SELECT id, entry_time FROM presences WHERE user_id = ? AND date = ?");
        $stmt->execute([$user_id, $current_date]);
        $presence = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($type === 'entry') {
            if ($presence) {
                $error = "Entrée déjà enregistrée pour aujourd'hui.";
            } else {
                $stmt = $db->prepare("INSERT INTO presences (user_id, date, entry_time, status) VALUES (?, ?, ?, 'present')");
                $stmt->execute([$user_id, $current_date, $current_time]);
                $success = "Entrée enregistrée !";
            }
        } elseif ($type === 'exit') {
            if (!$presence || !$presence['entry_time']) {
                $error = "Aucune entrée enregistrée pour aujourd'hui.";
            } else {
                $stmt = $db->prepare("UPDATE presences SET exit_time = ?, status = 'present' WHERE id = ?");
                $stmt->execute([$current_time, $presence['id']]);
                // Calculate hours worked
                $entry_time = new DateTime($presence['entry_time']);
                $exit_time = new DateTime($current_time);
                $interval = $entry_time->diff($exit_time);
                $hours_worked = $interval->h + ($interval->i / 60);
                $success = "Sortie enregistrée ! Heures travaillées : " . number_format($hours_worked, 2) . "h";
            }
        }
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Fetch user data
try {
    $stmt = $db->prepare("SELECT nom, prenom, photo, est_actif FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user is active
    if (!$user['est_actif']) {
        $error = "Votre compte est inactif. Contactez l'administrateur.";
    }
    
    // Récupérer les présences de l'utilisateur
    $stmt = $db->prepare("SELECT date, entry_time, exit_time, status FROM presences WHERE user_id = ? ORDER BY date DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $user_presences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur DB: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="header">
        <div class="profile-container">
            <img src="uploads/<?php echo htmlspecialchars($user['photo'] ?: 'default_profile.png'); ?>" alt="Profile Photo" class="profile-photo">
            <div><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?> (Utilisateur)</div>
        </div>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>

    <div class="container">
        <h2>Tableau de Bord</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($user['est_actif']): ?>
            <div>
                <h3>Marquer Présence</h3>
                <form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;" onsubmit="return confirm('Confirmer l\'enregistrement de la présence ?');">
                    <input type="hidden" name="action" value="mark_presence">
                    <button type="submit" name="type" value="entry" class="btn btn-primary">Entrée</button>
                    <button type="submit" name="type" value="exit" class="btn btn-primary">Sortie</button>
                </form>
            </div>
            
            <h3>Mes Présences</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure d'arrivée</th>
                        <th>Heure de départ</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($user_presences)): ?>
                        <?php foreach ($user_presences as $presence): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($presence['date']); ?></td>
                                <td><?php echo htmlspecialchars($presence['entry_time'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($presence['exit_time'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo $presence['status'] === 'present' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $presence['status'] === 'present' ? 'Présent' : 'Absent'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Aucune présence enregistrée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script>
        // Détection de fermeture de l'application/navigateur
        window.addEventListener('beforeunload', function(e) {
            // Envoyer une requête pour marquer une absence si nécessaire
            const data = new FormData();
            data.append('user_id', '<?php echo $_SESSION["user_id"]; ?>');
            
            // Utiliser navigator.sendBeacon pour envoyer une requête asynchrone même pendant la fermeture
            navigator.sendBeacon('mark_absence.php', data);
        });

        // Pour les mobiles, détecter aussi le changement d'onglet/d'application
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                // L'utilisateur a changé d'onglet ou est passé à une autre application
                const data = new FormData();
                data.append('user_id', '<?php echo $_SESSION["user_id"]; ?>');
                
                // Utiliser fetch avec keepalive pour garantir l'envoi
                fetch('mark_absence.php', {
                    method: 'POST',
                    body: data,
                    keepalive: true
                });
            }
        });
    </script>
</body>
</html>