<?php
include_once '../../controllers/ServiceController.php';
include_once '../../models/ServiceModel.php';


$sc = new ServiceController();

$error = '';

if (isset($_POST['Save'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? '';

    if (empty($name) || empty($description) || empty($icon)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        $image = '';
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $imagePath = "uploads/" . basename($_FILES["image"]["name"]);
            }
        }

        $service = new ServiceModel(
            null,
            $name,
            $description,
            $icon,
            '',
            $imagePath
        );

        $sc->addService($service);
        header('Location:list_services.php');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Service</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../uploads/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">

            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="../frontoffice/logout.php" class="nav-link logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>

        </nav>
    </header>

    <main class="main-content">
        <section class="hero-section">
            <h1 class="hero-title">Ajouter un nouveau service</h1>
            <p class="hero-description">
                Remplissez le formulaire ci-dessous pour ajouter un nouveau service à votre catalogue.
            </p>
        </section>

        <section class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <div class="form-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h2>Informations du service</h2>
                    <p>Remplissez tous les champs obligatoires (*) pour créer un nouveau service</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div
                        style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="modern-form" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag"></i>
                            Nom du service <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="text" id="name" name="name" placeholder="Ex: Développement Web">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i>
                            Description <span class="required">*</span>
                        </label>
                        <div class="textarea-wrapper">
                            <i class="fas fa-align-left textarea-icon"></i>
                            <textarea id="description" name="description" rows="5"
                                placeholder="Décrivez le service en détail..."></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="icon">
                            <i class="fas fa-icons"></i>
                            Icône Font Awesome <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-icons input-icon"></i>
                            <input type="text" id="icon" name="icon" placeholder="Ex: fas fa-code">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">
                            <i class="fas fa-image"></i>
                            Image de fond
                        </label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-area" id="fileUploadArea">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p class="file-upload-text">
                                    <span class="file-upload-main">Cliquez pour téléverser</span>
                                    <span class="file-upload-sub">ou glissez-déposez une image ici</span>
                                </p>
                                <input type="file" id="image" name="image" accept="image/*" class="file-input"
                                    onchange="handleFileSelect(this)">
                            </div>
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <img id="previewImage" src="" alt="Aperçu">
                                <button type="button" class="file-remove" onclick="removeFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="submit" name="Save" class="btn-primary btn-submit">
                            <i class="fas fa-plus"></i>
                            <span>Ajouter le service</span>
                        </button>
                        <a href="dashboard.php" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            <span>Annuler</span>
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script>
        // JS Validation removed in favor of PHP validation

        function handleFileSelect(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('fileUploadArea').style.display = 'none';
                    document.getElementById('filePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function removeFile() {
            document.getElementById('image').value = '';
            document.getElementById('previewImage').src = '';
            document.getElementById('fileUploadArea').style.display = 'block';
            document.getElementById('filePreview').style.display = 'none';
        }
    </script>

</body>

</html>