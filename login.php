<?php
require_once 'includes/auth.php';

// Traitement du formulaire de connexion classique
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    try {
        $query = "SELECT * FROM users WHERE email = :email AND est_actif = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_photo'] = $user['photo'];
                
                $redirect = $user['role'] === 'admin' ? 'admin.php' : 'dashboard.php';
                header("Location: $redirect");
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $error = "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}

// Traitement de la connexion par QR code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_data'])) {
    $qr_data = filter_var($_POST['qr_data'], FILTER_SANITIZE_STRING);
    
    // Vérifier le format du QR code
    if (preg_match('/^PRESENCE_SYSTEM:USER:(\d+):([a-zA-Z\s]+):([a-zA-Z\s]+):(\d+)$/', $qr_data, $matches)) {
        $user_id = $matches[1];
        
        try {
            $query = "SELECT * FROM users WHERE id = :id AND qr_data = :qr_data";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':qr_data', $qr_data, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_photo'] = $user['photo'];
                
                $redirect = $user['role'] === 'admin' ? 'admin.php' : 'dashboard.php';
                header("Location: $redirect");
                exit;
            } else {
                $error = "Utilisateur non trouvé ou QR code non valide.";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données : " . $e->getMessage();
        }
    } else {
        $error = "Format du QR code invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Présence</title>
    <link rel="shortcut icon" href="logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Connexion</h2>
                <p>Choisissez votre méthode d'authentification</p>
            </div>
            
            <div class="auth-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="auth-tabs">
                    <div class="auth-tab active" id="email-tab">Email/Mot de passe</div>
                    <div class="auth-tab" id="qr-tab">QR Code (Caméra)</div>
                </div>
                
                <!-- Formulaire Email/Mot de passe -->
                <div class="auth-form active" id="email-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </form>
                </div>
                
                <!-- Formulaire QR Code -->
                <div class="auth-form" id="qr-form">
                    <div class="camera-permission">
                        <div class="camera-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <p>Autorisez l'accès à votre caméra pour scanner votre QR code</p>
                    </div>
                    
                    <div class="qr-scanner-container">
                        <div id="qr-reader"></div>
                        <div id="qr-result"></div>
                        
                        <div class="manual-qr-input">
                            <p>Ou entrez manuellement le code QR :</p>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="qr_data" placeholder="Collez les données du QR code ici" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Valider</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="auth-links">
                    <a href="forgot_password.php">Mot de passe oublié?</a>
                    <a href="register.php">Créer un compte</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Inclusion de la bibliothèque pour scanner les QR codes -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        // Gestion des onglets
        const tabs = {
            'email': 'email-form',
            'qr': 'qr-form'
        };

        Object.keys(tabs).forEach(tab => {
            document.getElementById(`${tab}-tab`).addEventListener('click', () => {
                switchTab(tab);
                if (tab === 'qr') initQRScanner();
            });
        });

        function switchTab(tab) {
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            
            document.getElementById(`${tab}-tab`).classList.add('active');
            document.getElementById(tabs[tab]).classList.add('active');
            
            // Arrêter le scanner si on change d'onglet
            if (tab !== 'qr' && window.qrScanner) {
                window.qrScanner.stop().catch(err => console.error("Erreur lors de l'arrêt du scanner:", err));
                window.qrScanner = null;
            }
        }

        // Initialisation du scanner QR
        function initQRScanner() {
            const qrReader = document.getElementById('qr-reader');
            qrReader.innerHTML = ''; // Réinitialiser le conteneur
            
            const html5Qrcode = new Html5Qrcode("qr-reader");

            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    const cameraId = cameras[0].id; // Utiliser la première caméra disponible

                    html5Qrcode.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 },
                            disableFlip: false
                        },
                        (decodedText) => {
                            // QR code détecté
                            document.getElementById('qr-result').innerHTML = `
                                QR code détecté avec succès! Connexion en cours...
                            `;
                            document.getElementById('qr-result').style.display = 'block';

                            // Soumettre automatiquement le formulaire
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '';
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'qr_data';
                            input.value = decodedText;
                            form.appendChild(input);
                            document.body.appendChild(form);
                            form.submit();

                            // Arrêter le scanner après détection
                            html5Qrcode.stop().catch(err => console.error("Erreur lors de l'arrêt du scanner:", err));
                        },
                        (errorMessage) => {
                            // Ignorer les erreurs de scan (ex. aucun QR code détecté)
                        }
                    ).catch(err => {
                        console.error("Erreur lors du démarrage du scanner:", err);
                        qrReader.innerHTML = `
                            <div class="alert alert-danger">
                                Impossible d'accéder à la caméra: ${err}. Vérifiez les permissions.
                            </div>
                        `;
                    });

                    window.qrScanner = html5Qrcode;
                } else {
                    qrReader.innerHTML = `
                        <div class="alert alert-danger">
                            Aucune caméra détectée. Vérifiez votre appareil.
                        </div>
                    `;
                }
            }).catch(err => {
                console.error("Erreur lors de la récupération des caméras:", err);
                qrReader.innerHTML = `
                    <div class="alert alert-danger">
                        Impossible d'accéder à la caméra: ${err}. Vérifiez les permissions.
                    </div>
                `;
            });
        }
    </script>
</body>
</html>