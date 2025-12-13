<?php
require_once '../../../controllers/UserController.php';
require_once '../../../controllers/ServiceController.php';

$userController = new UserController();
$userController->requireRole('admin');

// Fetch Stats Data
$serviceController = new ServiceController();
$services = $serviceController->obtenirTousLesServices();
$serviceCount = count($services);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medsense - Dashboard Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="stylesheet" href="../../frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="dashboard.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="../../frontoffice/logout.php" class="nav-link logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>

    <main class="dashboard-container">
        <!-- Hero -->
        <section class="hero-section">
            <h1 class="hero-title">Tableau de Bord</h1>
            <p class="hero-description">
                Bienvenue, Administrateur. Voici un aperçu de votre activité.
            </p>
        </section>

        <!-- Stats Row -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-info">
                    <h3>Services Actifs</h3>
                    <span class="stat-value"><?php echo $serviceCount; ?></span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="stat-info">
                    <h3>IA Medsense</h3>
                    <span class="stat-value">Active</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Date</h3>
                    <span class="stat-value" style="font-size: 1.1rem;"><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
        </div>

        <section class="dashboard-section">
            <!-- Display messages -->
            <?php if (isset($_GET['message']) || isset($_GET['error'])): ?>
                <div style="margin-bottom: 20px;">
                    <?php if (isset($_GET['message'])): ?>
                        <div class="message success"
                            style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px;">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="message error"
                            style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Market Radar Widget -->
            <div id="marketRadar" class="market-radar-widget"
                style="background: white; border-radius: 16px; padding: 30px; margin-bottom: 40px; display: none; position: relative; overflow: hidden;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
                    <div style="background: #f3e8ff; padding: 10px; border-radius: 10px; color: #7c3aed;">
                        <i class="fas fa-chart-line" style="font-size: 24px;"></i>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.4rem; color: #1e293b; font-weight: 700;">Radar des Tendances
                            IA</h2>
                        <span style="font-size: 0.9rem; color: #64748b;">Analyse prédictive de votre offre</span>
                    </div>
                    <span
                        style="margin-left: auto; background: #7c3aed; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Beta</span>
                </div>

                <div id="radarLoading"
                    style="color: #6b7280; font-style: italic; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-circle-notch fa-spin text-purple-600"></i> Analyse du marché en cours...
                </div>

                <div id="radarContent"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <!-- Trends will be injected here -->
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const radarWidget = document.getElementById('marketRadar');
                    const loader = document.getElementById('radarLoading');
                    const content = document.getElementById('radarContent');

                    // Show widget container
                    radarWidget.style.display = 'block';

                    fetch('get_market_trends_endpoint.php')
                        .then(response => response.json())
                        .then(data => {
                            loader.style.display = 'none';

                            if (Array.isArray(data) && data.length > 0) {
                                let delay = 0;
                                data.forEach(trend => {
                                    const card = document.createElement('div');
                                    // Clean generic styling for the cards inside radar
                                    card.style.cssText = `
                                        border: 1px solid #e2e8f0; 
                                        border-radius: 12px; 
                                        padding: 20px; 
                                        background: #f8fafc; 
                                        transition: all 0.3s;
                                        animation: fadeIn 0.5s ease forwards;
                                        animation-delay: ${delay}ms;
                                        opacity: 0;
                                    `;

                                    const fireIcon = trend.potential === 'High' ? '🔥' : '📈';
                                    const badgeColor = trend.potential === 'High' ? 'color: #ef4444; background: #fef2f2;' : 'color: #3b82f6; background: #eff6ff;';

                                    card.innerHTML = `
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                            <h3 style="margin: 0; color: #0f172a; font-size: 1.1rem; font-weight: 600;">${fireIcon} ${trend.service}</h3>
                                            <span style="${badgeColor} padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">${trend.potential} Potential</span>
                                        </div>
                                        <p style="margin: 0 0 15px 0; color: #475569; font-size: 0.95rem; line-height: 1.6;">${trend.reason}</p>
                                        <a href="add_service.php" style="display: inline-flex; align-items: center; gap: 5px; color: #7c3aed; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: gap 0.2s;">
                                            Créer ce service <i class="fas fa-arrow-right" style="font-size: 0.8em;"></i>
                                        </a>
                                    `;
                                    content.appendChild(card);

                                    // Add hover effect via JS since inline styles are tricky for pseudo-classes
                                    card.addEventListener('mouseenter', () => { card.style.transform = 'translateY(-2px)'; card.style.boxShadow = '0 4px 6px -1px rgba(0,0,0,0.1)'; });
                                    card.addEventListener('mouseleave', () => { card.style.transform = 'translateY(0)'; card.style.boxShadow = 'none'; });

                                    delay += 150;
                                });
                            } else {
                                content.innerHTML = '<p style="color: #64748b;">Tout semble à jour ! Aucune opportunité manquante détectée.</p>';
                            }
                        })
                        .catch(err => {
                            loader.innerHTML = '<span style="color: #ef4444;">Erreur de connexion à l\'IA.</span>';
                            console.error(err);
                        });
                });

                // Add keyframes for animation
                const styleSheet = document.createElement("style");
                styleSheet.innerText = `
                    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
                `;
                document.head.appendChild(styleSheet);
            </script>

            <!-- Navigation Links -->
            <div class="dashboard-nav">
                <a href="add_service.php" class="nav-button nav-button-add">
                    <div class="nav-button-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="nav-button-content">
                        <h3>Ajouter un service</h3>
                        <p>Créer un nouveau service</p>
                    </div>
                    <div class="nav-button-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                <a href="edit_service.php" class="nav-button nav-button-edit">
                    <div class="nav-button-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="nav-button-content">
                        <h3>Modifier un service</h3>
                        <p>Mettre à jour les infos</p>
                    </div>
                    <div class="nav-button-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                <a href="list_services.php" class="nav-button nav-button-list">
                    <div class="nav-button-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="nav-button-content">
                        <h3>Liste des services</h3>
                        <p>Voir le catalogue</p>
                    </div>
                    <div class="nav-button-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <a href="delete_service.php" class="nav-button nav-button-delete">
                    <div class="nav-button-icon">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="nav-button-content">
                        <h3>Supprimer un service</h3>
                        <p>Retirer du catalogue</p>
                    </div>
                    <div class="nav-button-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
        </section>
    </main>

</body>

</html>