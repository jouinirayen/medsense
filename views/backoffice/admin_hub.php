<?php
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../frontoffice/home/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Centrale - MedSense</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hub-container {
            max-width: 1200px;
            width: 100%;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            color: #1e3a8a;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .module-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            transition: transform 0.3s ease;
        }

        .module-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .service-icon {
            background: #eff6ff;
            color: #3b82f6;
        }

        .blog-icon {
            background: #f0fdf4;
            color: #16a34a;
        }

        .urgence-icon {
            background: #fef2f2;
            color: #dc2626;
        }

        .users-icon {
            background: #faf5ff;
            color: #9333ea;
        }

        .doctors-icon {
            background: #f0fdfa;
            color: #0d9488;
        }

        .stats-icon {
            background: #fff7ed;
            color: #ea580c;
        }

        .module-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }

        .module-desc {
            color: #64748b;
            line-height: 1.5;
        }

        .logout-btn {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: #fef2f2;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <a href="../frontoffice/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>

    <div class="hub-container">
        <div class="header">
            <h1>Bienvenue, Administrateur</h1>
            <p>Sélectionnez un module pour accéder à son tableau de bord</p>
        </div>

        <div class="modules-grid">
            <!-- Services Module -->
            <a href="dashboard_service/dashboard.php" class="module-card">
                <div class="icon-wrapper service-icon">
                    <i class="fas fa-hospital-user"></i>
                </div>
                <div class="module-title">Services & Créneaux</div>
                <div class="module-desc">
                    Gérez les services médicaux, les créneaux horaires et l'organisation générale.
                </div>
            </a>

            <!-- Blog Module -->
            <a href="../../back-office/dashboard.php" class="module-card">
                <div class="icon-wrapper blog-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="module-title">Gestion du Blog</div>
                <div class="module-desc">
                    Modérez les articles, les commentaires et gérez les publications de la communauté.
                </div>
            </a>

            <!-- Urgence Module -->
            <a href="../backoffice/reponse/admin_reclamations.php" class="module-card">
                <div class="icon-wrapper urgence-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="module-title">Réclamations</div>
                <div class="module-desc">
                    Traitez les signalements urgents et suivez les demandes d'assistance des utilisateurs.
                </div>
            </a>

            <!-- Users Module -->
            <a href="../backoffice/admin-users.php" class="module-card">
                <div class="icon-wrapper users-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="module-title">Gestion Utilisateurs</div>
                <div class="module-desc">
                    Gérez les comptes utilisateurs, les rôles, les statuts et les permissions d'accès.
                </div>
            </a>

            <!-- Doctors Module -->
            <a href="../backoffice/admin-medecins.php" class="module-card">
                <div class="icon-wrapper doctors-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="module-title">Gestion Médecins</div>
                <div class="module-desc">
                    Validez les diplômes, gérez les inscriptions et suivez l'activité des médecins.
                </div>
            </a>

            <!-- Statistics Module -->
            <a href="../backoffice/admin-reports-statistics.php" class="module-card">
                <div class="icon-wrapper stats-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="module-title">Rapports & Stats</div>
                <div class="module-desc">
                    Visualisez les performances, les inscriptions et les données clés de la plateforme.
                </div>
            </a>
        </div>
    </div>
</body>

</html>