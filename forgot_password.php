<?php
require_once 'includes/auth.php';

// Traitement du formulaire de réinitialisation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    
    // Vérifier si l'email existe
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Générer un token de réinitialisation (simplifié pour cet exemple)
        $token = bin2hex(random_bytes(32));
        
        // Envoyer un email (simulé ici)
        $message = "Pour réinitialiser votre mot de passe, cliquez sur le lien suivant: " . 
                  BASE_URL . "reset_password.php?token=$token&email=$email";
        
        // En production, vous utiliseriez une bibliothèque d'envoi d'emails
        $success = "Un email de réinitialisation a été envoyé à $email";
    } else {
        $error = "Aucun compte trouvé avec cet email.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Système de Présence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Mot de passe oublié</h2>
                <p>Réinitialisez votre mot de passe</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Réinitialiser le mot de passe</button>
            </form>
            
            <div class="auth-links">
                <a href="login.php">Se connecter</a>
                <a href="register.php">Créer un compte</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>