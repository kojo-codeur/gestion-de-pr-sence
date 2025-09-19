<?php
require_once 'includes/database.php';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les dernières actualités (exemple)
$news = [
    [
        'title' => 'Nouveau système de présence',
        'date' => '2025-01-15',
        'content' => 'Notre entreprise a mis en place un nouveau système de gestion de présence par QR code pour simplifier le processus.'
    ],
    [
        'title' => 'Formation employés',
        'date' => '2025-6-10',
        'content' => 'Une session de formation sur le nouveau système de présence aura lieu la semaine prochaine.'
    ],
    [
        'title' => 'Amélioration de la sécurité',
        'date' => '2025-11-05',
        'content' => 'Mise à jour des protocoles de sécurité pour protéger vos données personnelles.'
    ]
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Système de Gestion de Présence</title>
    <link rel="shortcut icon" href="logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" style="color: #fff" class="PresenceQR">
                <img class="PresenceQR" style="width: 40px; height: 40px; border-radius: 1px;" src="logo.jpg" alt="PresenceQR" srcset=""> PresenceQR
            </a>

            <ul class="nav-menu" id="nav-menu">
                <li class="nav-item">
                    <a href="#features" class="nav-link">Fonctionnalités</a>
                </li>
                <li class="nav-item">
                    <a href="#news" class="nav-link">Actualités</a>
                </li>
                <li class="nav-item">
                    <a href="#about" class="nav-link">À propos</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link">Contact</a>
                </li>
            </ul>

            <div class="nav-actions">
                <a href="login.php" class="btn btn-outline">Connexion</a>
                <a href="register.php" class="btn btn-primary">Inscription</a>
            </div>

            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-container">
            <div class="hero-content animate">
                <h1 class="hero-title">Gestion moderne de présence avec QR Code</h1>
                <p class="hero-description">Simplifiez la gestion des présences de votre entreprise avec notre système innovant basé sur la technologie QR code.</p>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-primary btn-lg">Commencer maintenant</a>
                    <a href="#features" class="btn btn-outline btn-lg">En savoir plus</a>
                </div>
            </div>
            <div class="hero-image animate delay-1">
                <div class="app-showcase">
                    <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                         alt="Application de présence QR">
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item animate delay-1">
                    <div class="stats-number">500+</div>
                    <div class="stats-label">Utilisateurs</div>
                </div>
                <div class="stat-item animate delay-2">
                    <div class="stats-number">98%</div>
                    <div class="stats-label">Précision</div>
                </div>
                <div class="stat-item animate delay-3">
                    <div class="stats-number">24/7</div>
                    <div class="stats-label">Disponibilité</div>
                </div>
                <div class="stat-item animate">
                    <div class="stats-number">100%</div>
                    <div class="stats-label">Sécurisé</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <div class="container">
            <div class="section-title animate">
                <h2 class="section-heading">Fonctionnalités principales</h2>
                <p class="section-subheading">Découvrez comment notre système peut transformer votre gestion de présence</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card animate delay-1">
                    <i class="fas fa-qrcode feature-icon"></i>
                    <h3 class="feature-title">QR Code Personnel</h3>
                    <p class="feature-description">Chaque utilisateur reçoit un QR code unique avec sa photo pour une identification sécurisée.</p>
                </div>
                
                <div class="feature-card animate delay-2">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3 class="feature-title">Statistiques en Temps Réel</h3>
                    <p class="feature-description">Visualisez les données de présence en temps réel avec des graphiques et rapports détaillés.</p>
                </div>
                
                <div class="feature-card animate delay-3">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <h3 class="feature-title">Interface Responsive</h3>
                    <p class="feature-description">Accédez à votre espace depuis n'importe quel appareil : ordinateur, tablette ou smartphone.</p>
                </div>
                
                <div class="feature-card animate delay-1">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <h3 class="feature-title">Sécurité Avancée</h3>
                    <p class="feature-description">Protection des données avec chiffrement et authentification multi-facteurs.</p>
                </div>
                
                <div class="feature-card animate delay-2">
                    <i class="fas fa-bell feature-icon"></i>
                    <h3 class="feature-title">Notifications</h3>
                    <p class="feature-description">Recevez des alertes pour les présences manquées ou les événements importants.</p>
                </div>
                
                <div class="feature-card animate delay-3">
                    <i class="fas fa-download feature-icon"></i>
                    <h3 class="feature-title">Export de Données</h3>
                    <p class="feature-description">Exportez vos données de présence aux formats PDF, Excel ou CSV pour un reporting facile.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="section news-section">
        <div class="container">
            <div class="section-title animate">
                <h2 class="section-heading">Actualités de l'entreprise</h2>
                <p class="section-subheading">Restez informé des dernières mises à jour et annonces</p>
            </div>
            
            <div class="news-grid">
                <?php foreach ($news as $item): ?>
                <div class="news-card animate">
                    <span class="news-date"><?php echo date('d/m/Y', strtotime($item['date'])); ?></span>
                    <h3 class="news-title"><?php echo $item['title']; ?></h3>
                    <p class="news-content"><?php echo $item['content']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-40 animate">
                <a href="#" class="btn btn-primary">Voir toutes les actualités</a>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="section">
        <div class="container">
            <div class="section-title animate">
                <h2 class="section-heading">Comment ça marche</h2>
                <p class="section-subheading">Quatre étapes simples pour gérer vos présences</p>
            </div>
            
            <div class="process-container">
                <div class="process-steps">
                    <div class="process-step animate">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Inscription</h4>
                            <p>Créez votre compte et téléchargez votre photo pour générer votre QR code personnel.</p>
                        </div>
                    </div>
                    
                    <div class="process-step animate delay-1">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Génération du QR Code</h4>
                            <p>Notre système génère un QR code unique qui vous identifie et contient vos informations.</p>
                        </div>
                    </div>
                    
                    <div class="process-step animate delay-2">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Scan de Présence</h4>
                            <p>Scannez votre QR code à l'entrée pour enregistrer automatiquement votre présence.</p>
                        </div>
                    </div>
                    
                    <div class="process-step animate delay-3">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Suivi et Reporting</h4>
                            <p>Consultez vos historiques de présence et générez des rapports détaillés.</p>
                        </div>
                    </div>
                </div>
                
                <div class="process-image animate">
                    <div class="app-showcase">
                        <img src="https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                             alt="Processus de scan QR">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title animate">Prêt à simplifier votre gestion de présence?</h2>
            <p class="cta-description animate">Rejoignez des centaines d'entreprises qui utilisent déjà notre système</p>
            <div class="animate">
                <a href="register.php" class="btn btn-primary btn-lg">Créer un compte</a>
                <a href="login.php" class="btn btn-outline btn-lg">Se connecter</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-info">
                    <span class="footer-brand">PresenceQR</span>
                    <p class="footer-description">Notre système de gestion de présence par QR code offre une solution moderne et efficace pour suivre les présences de vos employés.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-links-container">
                    <h5 class="footer-heading">Liens rapides</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="#features">Fonctionnalités</a></li>
                        <li><a href="#news">Actualités</a></li>
                        <li><a href="#about">À propos</a></li>
                    </ul>
                </div>
                
                <div class="footer-links-container">
                    <h5 class="footer-heading">Support</h5>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Guide d'utilisation</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">Conditions d'utilisation</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h5 class="footer-heading">Contact</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Rue Example, Paris</li>
                        <li><i class="fas fa-phone"></i> +33 1 23 45 67 89</li>
                        <li><i class="fas fa-envelope"></i> contact@presenceqr.fr</li>
                    </ul>
                </div>
            </div>
            
            <hr class="footer-divider">
            
            <div class="footer-bottom">
                <p>&copy; 2023 PresenceQR. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Menu mobile toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('nav-menu');
        
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
            });
        });
        
        // Animation on scroll
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.section-title, .feature-card, .news-card, .process-step, .stat-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>