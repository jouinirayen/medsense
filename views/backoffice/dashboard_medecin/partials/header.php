<?php
// Determine active page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="css/header_style.css?v=<?= time() ?>">

<header class="doc-header">
    <div class="doc-logo">
        <a href="afficher_rendezvous_medecin.php">
            <img src="../../images/logo.jpeg" alt="Logo Medsense">
        </a>
    </div>

    <nav class="doc-nav">
        <a href="afficher_rendezvous_medecin.php"
            class="doc-nav-link <?= $current_page == 'afficher_rendezvous_medecin.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Consultations</span>
        </a>

        <a href="manage_availability.php"
            class="doc-nav-link <?= $current_page == 'manage_availability.php' ? 'active' : '' ?>">
            <i class="fas fa-business-time"></i>
            <span>Mes Disponibilités</span>
        </a>

        <a href="reviews_manager.php"
            class="doc-nav-link <?= $current_page == 'reviews_manager.php' ? 'active' : '' ?>">
            <i class="fas fa-star"></i>
            <span>Avis & Réponses</span>
        </a>

        <a href="ai_scribe.php" class="doc-nav-link <?= $current_page == 'ai_scribe.php' ? 'active' : '' ?>">
            <i class="fas fa-sparkles"></i>
            <span>Assistant IA</span>
        </a>

        <a href="../../frontoffice/auth/profile.php" class="doc-nav-link">
            <i class="fas fa-user-circle"></i>
            <span>Mon compte</span>
        </a>
    </nav>

    <div class="doc-logout">
        <a href="../../frontoffice/logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
    </div>
</header>