<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medsense - Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo $cssFile; ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
        /* User Info Bar Styles */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .user-info-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8fafc;
            padding: 0.5rem 1.5rem;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            margin: 0 2rem;
            flex-grow: 1;
            max-width: 600px;
        }

        .user-info-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-greeting {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .user-status {
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>

<body>
    <header class="header">
        <div style="text-align: center;">
            <img src="../images/logo.jpeg" alt="Logo Medsense" style="height: 80px; width: auto;">
        </div>

        <div class="user-info-bar">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['prenom'] . '+' . $currentUser['nom']); ?>&background=0ea5e9&color=fff"
                alt="Avatar" class="user-info-avatar">
            <div class="user-info-text">
                <span class="user-greeting">Bonjour
                    <?php echo htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']); ?></span>
                <span class="user-status">Bienvenue sur votre espace personnel</span>
            </div>
        </div>

        <nav class="nav-links">
            <a href="front.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Accueil</span>
            </a>

            <a href="afficher_rendezvous_patient.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                <span>Mes Rendez-vous</span>
            </a>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../backoffice/dashboard.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-cog"></i></span>
                    <span>Admin</span>
                </a>
            <?php endif; ?>

            <a href="logout.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>
        </nav>
    </header>