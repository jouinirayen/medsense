<?php
session_start();
include_once '../../../controllers/AuthController.php';
include_once '../../../controllers/PasswordController.php';
include_once '../../../controllers/ProfileController.php';

$authController = new AuthController();
$passwordController = new PasswordController();
$profileController = new ProfileController();

if (!$authController->isLoggedIn()) {
    header('Location: sign-in.php');
    exit;
}

$user = $authController->getCurrentUser();
$isAdmin = $user && $user->estAdmin();
$isMedecin = $user && $user->estMedecin();

$profile_error = null;
$profile_success = null;
$photo_error = null;
$photo_success = null;
$password_error = null;
$password_success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $result = $profileController->updateProfile($user->getId(), $_POST);

        if ($result['success']) {
            $profile_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            $user = $authController->getCurrentUser();
        } else {
            $profile_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }

    if (isset($_POST['update_photo']) && isset($_FILES['photo_profil'])) {
        $result = $profileController->updateProfilePhoto($user->getId(), $_FILES['photo_profil']);

        if ($result['success']) {
            $photo_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            $user = $authController->getCurrentUser();
        } else {
            $photo_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }

    if (isset($_POST['delete_photo'])) {
        $result = $profileController->deleteProfilePhoto($user->getId());

        if ($result['success']) {
            $photo_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            $user = $authController->getCurrentUser();
        } else {
            $photo_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
        }
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $password_error = "Veuillez remplir tous les champs";
        } elseif ($new_password !== $confirm_password) {
            $password_error = "Les nouveaux mots de passe ne correspondent pas";
        } elseif (strlen($new_password) < 6) {
            $password_error = "Le nouveau mot de passe doit contenir au moins 6 caractères";
        } else {
            $result = $passwordController->changePassword($user->getId(), $current_password, $new_password);

            if ($result['success']) {
                $password_success = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
                $_POST = [];
            } else {
                $password_error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

function getProfilePhotoUrl($user)
{
    return $user->getPhotoProfilUrl();
}
$photo_url = getProfilePhotoUrl($user);

// Adapter pour le header partagé
$currentUser = $authController->getCurrentUser();
if ($currentUser && is_object($currentUser)) {
    // Si c'est un objet, on le convertit en tableau ou on accède aux getters
    $currentUserArray = [
        'id' => $currentUser->getId(),
        'nom' => $currentUser->getNom(),
        'prenom' => $currentUser->getPrenom(),
        'email' => $currentUser->getEmail(),
        'role' => $currentUser->getRole(),
        'status' => 'actif',
        'photo_url' => $photo_url
    ];
    // Le header utilise $currentUser comme tableau
    $currentUser = $currentUserArray;
}
$navPaths = [
    'accueil' => '../page-accueil/front.php',
    'rendezvous' => '../page-rendezvous/afficher_rendezvous_patient.php',
    'reclamation' => '/projet_unifie/views/frontoffice/reclamation/index.php',
    'admin' => '../../backoffice/dashboard_service/dashboard.php'
];
$activePage = 'profile';
$isIncluded = false;
?>

<?php if (isset($isMedecin) && $isMedecin): ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mon Profil - Medsense</title>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link rel="stylesheet"
            href="../../backoffice/dashboard_medecin/css/header_style.css?v=<?= time() ?>">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #f8fafc;
            }

            .doc-header {
                position: sticky;
                top: 0;
                z-index: 1000;
                width: 100%;
                left: 0;
                right: 0;
                box-sizing: border-box;
            }
        </style>
    </head>

    <body>
        <header class="doc-header">
            <div class="doc-logo">
                <a href="../../backoffice/dashboard_medecin/afficher_rendezvous_medecin.php">
                    <img src="../../images/logo.jpeg" alt="Logo Medsense">
                </a>
            </div>

            <nav class="doc-nav">
                <a href="../../backoffice/dashboard_medecin/afficher_rendezvous_medecin.php"
                    class="doc-nav-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Consultations</span>
                </a>

                <a href="../../backoffice/dashboard_medecin/manage_availability.php"
                    class="doc-nav-link">
                    <i class="fas fa-business-time"></i>
                    <span>Mes Disponibilités</span>
                </a>

                <a href="../../backoffice/dashboard_medecin/reviews_manager.php"
                    class="doc-nav-link">
                    <i class="fas fa-star"></i>
                    <span>Avis & Réponses</span>
                </a>

                <a href="../../backoffice/dashboard_medecin/ai_scribe.php" class="doc-nav-link">
                    <i class="fas fa-sparkles"></i>
                    <span>Assistant IA</span>
                </a>

                <a href="#" class="doc-nav-link active">
                    <i class="fas fa-user-circle"></i>
                    <span>Mon compte</span>
                </a>
            </nav>

            <div class="doc-logout">
                <a href="../../../controllers/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </header>
    <?php else: ?>
        <?php include '../page-accueil/partials/header.php'; ?>
    <?php endif; ?>

    <main class="main-content" style="padding: 2rem; background: #f8fafc; min-height: 60vh;">
        <div class="container" style="max-width: 900px; margin: 0 auto;">

            <?php if ($profile_success): ?>
                <div class="alert alert-success"
                    style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="fas fa-check-circle"></i> <?php echo $profile_success; ?>
                </div>
            <?php endif; ?>

            <?php if ($profile_error): ?>
                <div class="alert alert-danger"
                    style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $profile_error; ?>
                </div>
            <?php endif; ?>

            <?php if ($photo_success): ?>
                <div class="alert alert-success"
                    style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="fas fa-check-circle"></i> <?php echo $photo_success; ?>
                </div>
            <?php endif; ?>

            <?php if ($photo_error): ?>
                <div class="alert alert-danger"
                    style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $photo_error; ?>
                </div>
            <?php endif; ?>

            <div class="profile-card"
                style="background: white; border-radius: 16px; padding: 2.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">

                <div
                    style="display: flex; align-items: center; border-bottom: 2px solid #e2e8f0; margin-bottom: 2rem; padding-bottom: 1rem;">
                    <h2
                        style="margin: 0; font-size: 1.5rem; color: #1e293b; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-user-circle" style="color: #3b82f6;"></i>
                        Mon Profil
                    </h2>
                </div>

                <ul class="nav nav-tabs" id="myTab" role="tablist"
                    style="border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem; display: flex; gap: 0.5rem;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info-tab" type="button"
                            style="background: none; border: none; border-bottom: 2px solid #3b82f6; color: #3b82f6; font-weight: 600; padding: 0.75rem 1.5rem; font-size: 1rem;">
                            Informations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" type="button"
                            style="background: none; border: none; border-bottom: 2px solid transparent; color: #64748b; font-weight: 600; padding: 0.75rem 1.5rem; font-size: 1rem;">
                            Sécurité
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <!-- Info Tab -->
                    <div class="tab-pane fade show active" id="info" role="tabpanel">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="update_profile" value="1">

                            <div
                                style="margin-bottom: 2rem; display: flex; gap: 2rem; align-items: center; background: #f8fafc; padding: 1.5rem; border-radius: 12px;">
                                <div style="position: relative;">
                                    <div
                                        style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                        <?php if ($photo_url): ?>
                                            <img src="<?php echo htmlspecialchars($photo_url); ?>"
                                                alt="<?php echo htmlspecialchars($currentUser['prenom']); ?>"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div
                                                style="width: 100%; height: 100%; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: bold;">
                                                <?php echo strtoupper(substr($currentUser['prenom'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="flex: 1;">
                                    <h3
                                        style="margin: 0 0 0.5rem 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">
                                        Photo de profil</h3>
                                    <p style="margin: 0 0 1rem 0; color: #64748b; font-size: 0.875rem;">JPG, GIF ou PNG.
                                        1Mo
                                        max.</p>
                                    <div style="display: flex; gap: 0.75rem;">
                                        <label for="photo_upload" class="btn"
                                            style="background: white; border: 1px solid #cbd5e1; color: #334155; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 0.875rem;">
                                            <i class="fas fa-upload" style="margin-right: 0.5rem;"></i> Changer
                                        </label>
                                        <input type="file" id="photo_upload" name="photo_profil" style="display: none;"
                                            onchange="document.getElementById('photo_submit').click()">
                                        <button type="submit" name="update_photo" id="photo_submit"
                                            style="display: none;"></button>

                                        <?php if ($photo_url): ?>
                                            <button type="submit" name="delete_photo" class="btn"
                                                style="background: white; border: 1px solid #ef4444; color: #ef4444; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 0.875rem;">
                                                <i class="fas fa-trash" style="margin-right: 0.5rem;"></i> Supprimer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div
                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label
                                        style="display: block; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Prénom</label>
                                    <input type="text" name="prenom"
                                        value="<?php echo htmlspecialchars($currentUser['prenom']); ?>"
                                        class="form-control"
                                        style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                                </div>
                                <div class="form-group">
                                    <label
                                        style="display: block; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Nom</label>
                                    <input type="text" name="nom"
                                        value="<?php echo htmlspecialchars($currentUser['nom']); ?>"
                                        class="form-control"
                                        style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label
                                    style="display: block; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Email</label>
                                <input type="email" name="email"
                                    value="<?php echo htmlspecialchars($currentUser['email']); ?>" class="form-control"
                                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; background: #f1f5f9;">
                            </div>

                            <div
                                style="text-align: right; pt-4; border-top: 1px solid #e2e8f0; margin-top: 2rem; padding-top: 1.5rem;">
                                <button type="submit" class="btn-primary"
                                    style="background: #0f172a; color: white; padding: 0.75rem 2rem; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);">
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Tab -->
                    <div class="tab-pane fade" id="password" role="tabpanel" style="display: none;">
                        <form method="POST" action="">
                            <input type="hidden" name="change_password" value="1">

                            <?php if ($password_error): ?>
                                <div class="alert alert-danger"
                                    style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 1.5rem;">
                                    <?php echo $password_error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($password_success): ?>
                                <div class="alert alert-success"
                                    style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 1.5rem;">
                                    <?php echo $password_success; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label
                                    style="display: block; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Mot
                                    de passe actuel</label>
                                <input type="password" name="current_password" class="form-control"
                                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;"
                                    required>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label
                                    style="display: block; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Nouveau
                                    mot de passe</label>
                                <input type="password" name="new_password" class="form-control"
                                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;"
                                    required>
                            </div>

                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label
                                    style="display: block; font-weight: 500; color: #334155; margin-bottom: 0.5rem;">Confirmer
                                    le mot de passe</label>
                                <input type="password" name="confirm_password" class="form-control"
                                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;"
                                    required>
                            </div>

                            <div
                                style="text-align: right; pt-4; border-top: 1px solid #e2e8f0; margin-top: 2rem; padding-top: 1.5rem;">
                                <button type="submit" class="btn-primary"
                                    style="background: #0f172a; color: white; padding: 0.75rem 2rem; border-radius: 8px; border: none; font-weight: 600; cursor: pointer;">
                                    Mettre à jour le mot de passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const infoTab = document.getElementById('info-tab');
            const passwordTab = document.getElementById('password-tab');
            const infoContent = document.getElementById('info');
            const passwordContent = document.getElementById('password');

            infoTab.addEventListener('click', function (e) {
                e.preventDefault();
                infoTab.classList.add('active');
                infoTab.style.borderBottom = '2px solid #3b82f6';
                infoTab.style.color = '#3b82f6';

                passwordTab.classList.remove('active');
                passwordTab.style.borderBottom = '2px solid transparent';
                passwordTab.style.color = '#64748b';

                infoContent.style.display = 'block';
                passwordContent.style.display = 'none';
            });

            passwordTab.addEventListener('click', function (e) {
                e.preventDefault();
                passwordTab.classList.add('active');
                passwordTab.style.borderBottom = '2px solid #3b82f6';
                passwordTab.style.color = '#3b82f6';

                infoTab.classList.remove('active');
                infoTab.style.borderBottom = '2px solid transparent';
                infoTab.style.color = '#64748b';

                infoContent.style.display = 'none';
                passwordContent.style.display = 'block';
            });
        });
    </script>

    <?php include '../page-accueil/partials/footer.php'; ?>
</body>

</html>