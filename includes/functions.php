<?php
require_once 'database.php';

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function generateQRCode($data, $filename) {
    // Vérifier si le dossier existe, sinon le créer
    if (!file_exists(QR_CODE_DIR)) {
        mkdir(QR_CODE_DIR, 0777, true);
    }
    
    $filepath = QR_CODE_DIR . $filename;
    
    // Utiliser un service en ligne pour générer le QR code
    $url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($data);
    $image = file_get_contents($url);
    
    // Sauvegarder l'image
    file_put_contents($filepath, $image);
    
    return $filename;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getPresenceStats() {
    $database = new Database();
    $db = $database->getConnection();
    
    // Statistiques pour l'admin
    if (isAdmin()) {
        $query = "SELECT 
                    COUNT(DISTINCT user_id) as total_users,
                    COUNT(*) as total_presences,
                    (SELECT COUNT(*) FROM presences WHERE date = CURDATE()) as today_presences
                  FROM presences";
    } else {
        // Statistiques pour l'utilisateur
        $user_id = $_SESSION['user_id'];
        $query = "SELECT 
                    COUNT(*) as total_presences,
                    (SELECT COUNT(*) FROM presences WHERE user_id = $user_id AND date = CURDATE()) as today_presence
                  FROM presences 
                  WHERE user_id = $user_id";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>