<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Restrict access to admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'add_user') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];
            $photo = null;
            $qr_data = "PRESENCE:$nom:$prenom:" . time();

            // Handle photo upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo_name = time() . '_' . basename($_FILES['photo']['name']);
                $photo_path = $upload_dir . $photo_name;
                
                // effacer
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                    $photo = $photo_path;
                } else {
                    $error = "Erreur lors du téléchargement de la photo.";
                }
            }

            $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password, photo, role, qr_data, est_actif) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$nom, $prenom, $email, $password, $photo, $role, $qr_data]);
            $success = "Utilisateur ajouté !";
        } elseif ($_POST['action'] === 'activate_user') {
            $user_id = (int)$_POST['user_id'];
            $stmt = $db->prepare("UPDATE users SET est_actif = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->rowCount() > 0) {
                $success = "Utilisateur activé !";
            } else {
                $error = "Erreur lors de l'activation de l'utilisateur.";
            }
        } elseif ($_POST['action'] === 'deactivate_user') {
            $user_id = (int)$_POST['user_id'];
            $stmt = $db->prepare("UPDATE users SET est_actif = 0 WHERE id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->rowCount() > 0) {
                $success = "Utilisateur désactivé !";
            } else {
                $error = "Erreur lors de la désactivation de l'utilisateur.";
            }
        } elseif ($_POST['action'] === 'mark_presence') {
            $user_id = $_SESSION['user_id'];
            $type = $_POST['type'];
            $current_time = date('H:i:s');
            $current_date = date('Y-m-d');

            // Vérifier si une entrée existe déjà pour aujourd'hui
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
                    // Calcul des heures travaillées
                    $entry_time = new DateTime($presence['entry_time']);
                    $exit_time = new DateTime($current_time);
                    $interval = $entry_time->diff($exit_time);
                    $hours_worked = $interval->h + ($interval->i / 60); // Convertir en heures décimales
                    $success = "Sortie enregistrée ! Heures travaillées : " . number_format($hours_worked, 2) . "h";
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Fetch users and stats
try {
    $stmt = $db->query("SELECT id, nom, prenom, email, photo, role, est_actif, created_at FROM users ORDER BY nom");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Préparer tableau des stats
    $admin_stats = [
        'total_users' => 0,
        'today_presences' => 0,
        'active_users' => 0,
        'inactive_users' => 0
    ];

    $query1 = $db->query("SELECT COUNT(*) FROM users");
    if ($query1) {
        $admin_stats['total_users'] = $query1->fetchColumn();
    }

    $query2 = $db->query("SELECT COUNT(*) FROM presences WHERE DATE(date) = CURDATE() AND entry_time IS NOT NULL");
    if ($query2) {
        $admin_stats['today_presences'] = $query2->fetchColumn();
    }
    
    $query3 = $db->query("SELECT COUNT(*) FROM users WHERE est_actif = 1");
    if ($query3) {
        $admin_stats['active_users'] = $query3->fetchColumn();
    }
    
    $query4 = $db->query("SELECT COUNT(*) FROM users WHERE est_actif = 0");
    if ($query4) {
        $admin_stats['inactive_users'] = $query4->fetchColumn();
    }

} catch (PDOException $e) {
    $error = "Erreur DB: " . $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="header">
        <div class="profile-container">
            <?php
            // Fetch user photo
            $stmt = $db->prepare("SELECT photo FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $photo = $user['photo'] ? $user['photo'] : 'default_profile.png';
            ?>
            <img src="<?php echo htmlspecialchars($photo); ?>" alt="Profile Photo" class="profile-photo">
            <div><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</div>
        </div>
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>

    <div class="container">
        <h2>Panneau d'Administration</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $admin_stats['total_users']; ?></h3>
                <p>Utilisateurs Totaux</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $admin_stats['active_users']; ?></h3>
                <p>Utilisateurs Actifs</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $admin_stats['inactive_users']; ?></h3>
                <p>Utilisateurs Inactifs</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $admin_stats['today_presences']; ?></h3>
                <p>Présences Aujourd'hui</p>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="presence">Gestion des Présences</div>
            <div class="tab" data-tab="users">Gestion des Utilisateurs</div>
        </div>

        <div class="tab-content active" id="presence-tab">
            <h3>Marquer Présence</h3>
            <form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;" onsubmit="return confirm('Confirmer l\'enregistrement de la présence ?');">
                <input type="hidden" name="action" value="mark_presence">
                <button type="submit" name="type" value="entry" class="btn btn-primary">Entrée</button>
                <button type="submit" name="type" value="exit" class="btn btn-primary">Sortie</button>
            </form>
            
            <h3>Mes Présences</h3>
            <?php
            // Récupérer les présences de l'admin
            try {
                $stmt = $db->prepare("SELECT date, entry_time, exit_time, status FROM presences WHERE user_id = ? ORDER BY date DESC LIMIT 10");
                $stmt->execute([$_SESSION['user_id']]);
                $user_presences = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "Erreur DB: " . $e->getMessage();
            }
            ?>
            
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
        </div>

        <div class="tab-content" id="users-tab">
            <h3>Utilisateurs</h3>
            <button class="btn btn-primary" id="addUserBtn"><i class="fas fa-plus"></i> Ajouter un utilisateur</button>
            <table class="table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($user['photo'] ?: 'logo.jpg'); ?>" alt="Photo" width="40" height="40" style="border-radius: 50%; object-fit: cover;"></td>
                            <td><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge <?php echo $user['role'] === 'admin' ? 'badge-success' : 'badge-info'; ?>">
                                <?php echo ucfirst($user['role']); ?></span></td>
                            <td><span class="badge <?php echo $user['est_actif'] ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo $user['est_actif'] ? 'Actif' : 'Inactif'; ?></span></td>
                            <td class="action-buttons">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('<?php echo $user['est_actif'] ? 'Désactiver' : 'Activer'; ?> cet utilisateur ?');">
                                    <input type="hidden" name="action" value="<?php echo $user['est_actif'] ? 'deactivate_user' : 'activate_user'; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn <?php echo $user['est_actif'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <i class="fas <?php echo $user['est_actif'] ? 'fa-times' : 'fa-check'; ?>"></i>
                                    </button>
                                </form>
                                <button class="view-presence-btn" data-user-id="<?php echo $user['id']; ?>" data-user-name="<?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>">
                                    <i class="fas fa-calendar"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal pour ajouter un utilisateur -->
        <div class="modal" id="addUserModal">
            <div class="modal-content">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <h3>Ajouter Utilisateur</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_user">
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Photo de profil</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rôle</label>
                        <select class="form-control" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>

        <!-- Modal pour voir les présences d'un utilisateur -->
        <div class="modal" id="viewPresenceModal">
            <div class="modal-content">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <h3 id="presenceModalTitle">Présences de l'utilisateur</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div id="presenceModalContent">
                    <p>Chargement des données...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion des onglets
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab + '-tab').classList.add('active');
            });
        });

        // Modal pour ajouter un utilisateur
        document.getElementById('addUserBtn').addEventListener('click', () => {
            document.getElementById('addUserModal').style.display = 'flex';
        });

        // Modal pour voir les présences
        document.querySelectorAll('.view-presence-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = btn.dataset.userId;
                const userName = btn.dataset.userName;
                
                document.getElementById('presenceModalTitle').textContent = `Présences de ${userName}`;
                document.getElementById('presenceModalContent').innerHTML = '<p>Chargement des données...</p>';
                document.getElementById('viewPresenceModal').style.display = 'flex';
                
                // Charger les présences via AJAX
                fetch(`get_user_presences.php?user_id=${userId}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('presenceModalContent').innerHTML = data;
                    })
                    .catch(error => {
                        document.getElementById('presenceModalContent').innerHTML = '<p>Erreur lors du chargement des données.</p>';
                    });
            });
        });

        // Fermer les modals
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.modal').style.display = 'none';
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

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