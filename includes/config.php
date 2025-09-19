<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'qr_presence_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de l'application
define('BASE_URL', 'http://localhost/presence/');
define('QR_CODE_DIR', __DIR__ . '/../assets/qrcodes/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Créer les dossiers s'ils n'existent pas
if (!file_exists(QR_CODE_DIR)) {
    mkdir(QR_CODE_DIR, 0777, true);
}
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
?>