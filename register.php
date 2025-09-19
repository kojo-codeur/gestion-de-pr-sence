<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/database.php';

// Inclure la bibliothèque QR Code
require_once 'phpqrcode/qrlib.php';

// Fonction pour générer un vrai QR code avec la bibliothèque pure PHP
function generateSimpleQRCode($data, $filename, $size = 200) {
    // Créer le répertoire s'il n'existe pas
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Niveau d'erreur L, taille des modules ajustée pour ~200x200 px (4 pixels par module)
    QRcode::png($data, $filename, QR_ECLEVEL_L, 4, 2);
    return basename($filename);
}

// Fonction pour créer une carte d'identité avec photo, nom, prénom et QR code
function generateIdentityCard($nom, $prenom, $photo_path, $qr_path, $filename) {
    // Créer le répertoire s'il n'existe pas
    $dir = dirname($filename);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Dimensions de la carte
    $card_width = 600;
    $card_height = 400;
    
    // Créer l'image de la carte
    $card = imagecreatetruecolor($card_width, $card_height);
    
    // Couleurs
    $white = imagecolorallocate($card, 255, 255, 255);
    $black = imagecolorallocate($card, 0, 0, 0);
    $blue = imagecolorallocate($card, 41, 128, 185);
    $light_gray = imagecolorallocate($card, 240, 240, 240);
    
    // Remplir le fond
    imagefilledrectangle($card, 0, 0, $card_width, $card_height, $white);
    
    // Ajouter une bande bleue en haut
    imagefilledrectangle($card, 0, 0, $card_width, 80, $blue);
    
    // Ajouter un fond gris pour la photo
    $photo_bg_size = 150;
    $photo_bg_x = 30;
    $photo_bg_y = 115;
    imagefilledrectangle($card, $photo_bg_x, $photo_bg_y, $photo_bg_x + $photo_bg_size, $photo_bg_y + $photo_bg_size, $light_gray);
    
    // Charger et ajouter la photo si elle existe
    if (!empty($photo_path) && file_exists($photo_path)) {
        $photo_info = getimagesize($photo_path);
        $photo_type = $photo_info[2];
        
        switch ($photo_type) {
            case IMAGETYPE_JPEG:
                $photo = imagecreatefromjpeg($photo_path);
                break;
            case IMAGETYPE_PNG:
                $photo = imagecreatefrompng($photo_path);
                break;
            case IMAGETYPE_GIF:
                $photo = imagecreatefromgif($photo_path);
                break;
            default:
                $photo = false;
        }
        
        if ($photo !== false) {
            // Redimensionner la photo pour s'adapter au cadre
            $photo_width = imagesx($photo);
            $photo_height = imagesy($photo);
            
            $new_photo_size = 140;
            $new_photo = imagecreatetruecolor($new_photo_size, $new_photo_size);
            imagecopyresampled($new_photo, $photo, 0, 0, 0, 0, $new_photo_size, $new_photo_size, $photo_width, $photo_height);
            
            // Ajouter la photo à la carte
            imagecopy($card, $new_photo, $photo_bg_x + 5, $photo_bg_y + 5, 0, 0, $new_photo_size, $new_photo_size);
            
            imagedestroy($photo);
            imagedestroy($new_photo);
        }
    }
    
    // Ajouter le QR code
    if (!empty($qr_path) && file_exists($qr_path)) {
        $qr_code = imagecreatefrompng($qr_path);
        $qr_size = 120;
        $qr_x = $card_width - $qr_size - 30;
        $qr_y = $card_height - $qr_size - 30;
        imagecopyresized($card, $qr_code, $qr_x, $qr_y, 0, 0, $qr_size, $qr_size, imagesx($qr_code), imagesy($qr_code));
        imagedestroy($qr_code);
    }
    
    // Police (utiliser une police par défaut)
    $font = 5; // Police GD intégrée
    $large_font = 5;
    
    // Ajouter le titre
    $title = "CARTE D'IDENTITE";
    $title_width = imagefontwidth($large_font) * strlen($title);
    $title_x = ($card_width - $title_width) / 2;
    imagestring($card, $large_font, $title_x, 25, $title, $white);
    
    // Ajouter le nom et prénom
    $full_name = strtoupper($nom . " " . $prenom);
    $name_x = 200;
    $name_y = 120;
    imagestring($card, $font, $name_x, $name_y, "NOM: " . $full_name, $black);
    
    // Ajouter d'autres informations
    imagestring($card, $font, $name_x, $name_y + 30, "STATUT: Utilisateur", $black);
    imagestring($card, $font, $name_x, $name_y + 60, "DATE: " . date('d/m/Y'), $black);
    
    // Ajouter un numéro d'identification
    $id_text = "ID: " . strtoupper(substr($nom, 0, 3) . substr($prenom, 0, 3) . date('Y'));
    imagestring($card, $font, $name_x, $name_y + 90, $id_text, $black);
    
    // Ajouter un footer
    $footer = "Système de Présence - " . date('Y');
    $footer_width = imagefontwidth($font) * strlen($footer);
    $footer_x = ($card_width - $footer_width) / 2;
    imagestring($card, $font, $footer_x, $card_height - 20, $footer, $black);
    
    // Sauvegarder la carte
    imagepng($card, $filename);
    imagedestroy($card);
    
    return basename($filename);
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom']);
    $prenom = sanitize($_POST['prenom']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            // Traitement de l'upload de photo
            $photo_name = null;
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Créer le répertoire uploads s'il n'existe pas
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                
                $photo_tmp = $_FILES['photo']['tmp_name'];
                $photo_name = time() . '_' . basename($_FILES['photo']['name']);
                $photo_path = UPLOAD_DIR . $photo_name;
                move_uploaded_file($photo_tmp, $photo_path);
            }
            
            // Hash du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertion de l'utilisateur
            $query = "INSERT INTO users (nom, prenom, email, password, photo) 
                      VALUES (:nom, :prenom, :email, :password, :photo)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':photo', $photo_name);
            
            if ($stmt->execute()) {
                $user_id = $db->lastInsertId();
                
                // Créer le répertoire qrcodes s'il n'existe pas
                if (!is_dir(QR_CODE_DIR)) {
                    mkdir(QR_CODE_DIR, 0755, true);
                }
                
                // Générer le QR code avec les données de l'utilisateur
                $qr_data = "PRESENCE_SYSTEM:USER:$user_id:$nom:$prenom:" . time();
                $qr_filename = "user_$user_id.png";
                $qr_path = QR_CODE_DIR . $qr_filename;
                
                // Créer le QR code avec la vraie bibliothèque
                generateSimpleQRCode($qr_data, $qr_path);
                
                // Générer la carte d'identité
                $card_filename = "card_$user_id.png";
                $card_path = QR_CODE_DIR . $card_filename;
                generateIdentityCard($nom, $prenom, $photo_path, $qr_path, $card_path);
                
                // Mettre à jour l'utilisateur avec le QR code, les données QR et la carte
                $update_query = "UPDATE users SET qr_code = :qr_code, qr_data = :qr_data, identity_card = :identity_card WHERE id = :id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':qr_code', $qr_filename);
                $update_stmt->bindParam(':qr_data', $qr_data);
                $update_stmt->bindParam(':identity_card', $card_filename);
                $update_stmt->bindParam(':id', $user_id);
                
                if ($update_stmt->execute()) {
                    $success = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
                    
                    // Afficher le QR code et la carte générés pour téléchargement
                    $qr_generated = true;
                    $card_generated = true;
                    $qr_file_path = "assets/qrcodes/" . $qr_filename;
                    $card_file_path = "assets/qrcodes/" . $card_filename;
                } else {
                    $error = "Erreur lors de la génération du QR code ou de la carte.";
                }
            } else {
                $error = "Une erreur s'est produite lors de la création du compte.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Système de Présence</title>
    <link rel="shortcut icon" href="logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }
        
        .preview-item {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        
        .download-btn {
            margin-top: 10px;
        }
        
        .success-box {
            background-color: #f0fff0;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Inscription</h2>
                <p>Créez votre compte pour utiliser le système</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-box">
                    <h4 class="text-success"><i class="fas fa-check-circle"></i>&nbsp;Inscription réussie!</h4>
                    <p class="mb-3"><?php echo htmlspecialchars($success); ?></p>
                    
                    <?php if (isset($qr_generated) && $qr_generated && isset($card_generated) && $card_generated): ?>
                        <div class="card-container">
                            <div class="preview-item">
                                <h5>Votre Carte d'Identité</h5>
                                <div class="card-preview">
                                    <img src="<?php echo htmlspecialchars($card_file_path); ?>?t=<?php echo time(); ?>" alt="Carte d'identité" style="max-width: 300px;">
                                </div>
                                <a href="<?php echo htmlspecialchars($card_file_path); ?>" download class="btn btn-primary download-btn">
                                    <i class="fas fa-download"></i>&nbsp;Télécharger la Carte
                                </a>
                            </div>
                            
                            <div class="preview-item">
                                <h5>Votre QR Code personnel</h5>
                                <div class="qr-preview">
                                    <img src="<?php echo htmlspecialchars($qr_file_path); ?>?t=<?php echo time(); ?>" alt="QR Code">
                                </div>
                                <a href="<?php echo htmlspecialchars($qr_file_path); ?>" download class="btn btn-success download-btn">
                                    <i class="fas fa-download"></i>&nbsp;Télécharger le QR Code
                                </a>
                            </div>
                        </div>
                        <p class="small text-muted">Scannez le QR code pour marquer votre présence</p>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="btn btn-primary">Se connecter</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($success)): ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="photo" class="form-label">Photo de profil</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                    <div class="form-text">Téléchargez une photo pour votre carte d'identité (recommandé)</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>&nbsp;Créer mon compte
                </button>
            </form>
            
            <div class="auth-links">
                <a href="login.php">Déjà un compte? Se connecter</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>