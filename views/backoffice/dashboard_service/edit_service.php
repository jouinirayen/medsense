<?php
include_once '../../../controllers/ServiceController.php';



$sc = new ServiceController();

$error = '';

if (isset($_POST['Save'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['icon'] ?? '';

    if (empty($name) || empty($description) || empty($icon)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../../uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $imagePath = "uploads/" . basename($_FILES["image"]["name"]);
            }
        } else {
            $imagePath = $_POST['old_image'] ?? '';
        }

        $service = [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'link' => '',
            'image' => $imagePath
        ];

        $sc->updateService($_POST['id'], $service);
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
    <title>Modifier un Service</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://image.pollinations.ai" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/projet2025/views/frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet"
        href="/projet2025/views/backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
    <style>
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
            font-weight: 500;
        }

        .input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-section">
            <img src="../../images/logo.jpeg" alt="Logo Medsense" style="height: 50px; width: auto;">
        </div>
        <nav class="nav-links">

            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="../../frontoffice/logout.php" class="nav-link logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span>Déconnexion</span>
            </a>

        </nav>
    </header>

    <main class="main-content">
        <?php
        if (isset($_GET['id'])) {
            $service = $sc->obtenirServiceParId($_GET['id']);
            ?>
            <section class="hero-section">
                <h1 class="hero-title">Modifier un service</h1>
                <p class="hero-description">
                    Modifiez les informations du service ci-dessous.
                </p>
            </section>

            <section class="form-section">
                <div class="form-container">
                    <div class="form-header">
                        <div class="form-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h2>Modifier les informations du service</h2>
                        <p>Mettez à jour les détails du service ci-dessous</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div
                            style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="modern-form" enctype="multipart/form-data" id="editServiceForm"
                        novalidate>
                        <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo $service['image']; ?>">

                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-tag"></i>
                                Nom du service <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <i class="fas fa-tag input-icon"></i>
                                <input type="text" id="name" name="name" value="<?php echo $service['name']; ?>">
                            </div>
                            <span class="error-message" id="error-name">Le nom doit contenir au moins 3 caractères.</span>
                        </div>

                        <div class="form-group">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Description <span class="required">*</span>
                            </label>
                            <div class="textarea-wrapper">
                                <i class="fas fa-align-left textarea-icon"></i>
                                <textarea id="description" name="description"
                                    rows="5"><?php echo $service['description']; ?></textarea>
                            </div>
                            <span class="error-message" id="error-description">La description doit contenir au moins 10
                                caractères.</span>
                        </div>

                        <div class="form-group">
                            <label for="icon">
                                <i class="fas fa-icons"></i>
                                Icône Font Awesome <span class="required">*</span>
                            </label>
                            <div class="input-wrapper" style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-icons input-icon"></i>
                                <input type="text" id="icon" name="icon" value="<?php echo $service['icon']; ?>"
                                    style="flex: 1;" placeholder="Ex: fas fa-code">
                                <div id="iconPreview"
                                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 8px; border: 1px solid #e2e8f0; color: #3b82f6;">
                                    <i class="<?php echo $service['icon']; ?>"></i>
                                </div>
                            </div>
                            <span class="error-message" id="error-icon">Format invalide (doit commencer par 'fas fa-', 'far
                                fa-' ou 'fab fa-').</span>
                        </div>


                        <div class="form-group">
                            <label for="image">
                                <i class="fas fa-image"></i>
                                Image de fond
                            </label>
                            <?php if (!empty($service['image'])): ?>
                                <div class="current-image-preview">
                                    <p class="current-image-label">Image actuelle :</p>
                                    <img src="../../<?php echo $service['image']; ?>" alt="Image actuelle"
                                        class="current-image">
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-area" id="fileUploadArea">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="file-upload-main">Cliquez pour téléverser une nouvelle image</span>
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
                            <span class="error-message" id="error-image">Veuillez sélectionner une image valide (JPG,
                                PNG).</span>

                        </div>

                        <div class="form-actions">
                            <button type="submit" name="Save" class="btn-primary btn-submit">
                                <i class="fas fa-save"></i>
                                <span>Enregistrer les modifications</span>
                            </button>
                            <a href="edit_service.php" class="btn-secondary">
                                <i class="fas fa-times"></i>
                                <span>Annuler</span>
                            </a>
                        </div>
                    </form>
                </div>
            </section>
            <?php
        } else {
            $services = $sc->obtenirTousLesServices();
            ?>
            <section class="hero-section">
                <h1 class="hero-title">Modifier un service</h1>
                <p class="hero-description">
                    Sélectionnez un service dans la liste ci-dessous pour le modifier.
                </p>
            </section>

            <section class="table-section">
                <!-- Search Bar -->
                <div class="search-container" style="max-width: 400px; margin-bottom: 20px;">
                    <div class="search-box-wrapper" style="background: white; border: 1px solid #e2e8f0;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="serviceSearch" class="search-input" placeholder="Rechercher un service..."
                            onkeyup="filterServices()">
                        <a href="#" class="search-clear" onclick="clearSearch()" style="display: none;" id="clearSearchBtn">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>

                <div class="table-container">
                    <table class="services-edit-table" id="servicesTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td>
                                        <?php if ($s['image']): ?>
                                            <img src="../../<?php echo $s['image']; ?>" alt="Service" class="service-thumbnail">
                                        <?php else: ?>
                                            <span class="no-image"><i class="fas fa-image"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $s['name']; ?></td>
                                    <td><?php echo substr($s['description'], 0, 50) . '...'; ?></td>
                                    <td>
                                        <a href="edit_service.php?id=<?php echo $s['id']; ?>" class="btn-edit-modern">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="noResults" style="display: none; text-align: center; padding: 20px; color: #64748b;">
                        Aucun service trouvé.
                    </div>
                </div>
            </section>

            <script>
                function filterServices() {
                    const input = document.getElementById('serviceSearch');
                    const filter = input.value.toLowerCase();
                    const table = document.getElementById('servicesTable');
                    const tr = table.getElementsByTagName('tr');
                    const clearBtn = document.getElementById('clearSearchBtn');
                    const noResults = document.getElementById('noResults');
                    let visibleCount = 0;

                    if (filter.length > 0) {
                        clearBtn.style.display = 'flex';
                    } else {
                        clearBtn.style.display = 'none';
                    }

                    for (let i = 1; i < tr.length; i++) {
                        const tdName = tr[i].getElementsByTagName('td')[1]; // Name column
                        const tdDesc = tr[i].getElementsByTagName('td')[2]; // Description column

                        if (tdName || tdDesc) {
                            const txtValueName = tdName.textContent || tdName.innerText;
                            const txtValueDesc = tdDesc.textContent || tdDesc.innerText;

                            if (txtValueName.toLowerCase().indexOf(filter) > -1 || txtValueDesc.toLowerCase().indexOf(filter) > -1) {
                                tr[i].style.display = "";
                                visibleCount++;
                            } else {
                                tr[i].style.display = "none";
                            }
                        }
                    }

                    if (visibleCount === 0 && filter.length > 0) {
                        noResults.style.display = 'block';
                    } else {
                        noResults.style.display = 'none';
                    }
                }

                function clearSearch() {
                    const input = document.getElementById('serviceSearch');
                    input.value = '';
                    filterServices();
                    input.focus();
                }
            </script>
            <?php
        }
        ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('editServiceForm');
            // If we are not on the edit page (e.g. list view), form might be null
            if (!form) return;

            const nameInput = document.getElementById('name');
            const descInput = document.getElementById('description');
            const iconInput = document.getElementById('icon');
            const imageInput = document.getElementById('image');

            // Validation Functions
            function validateName() {
                const val = nameInput.value.trim();
                const errorSpan = document.getElementById('error-name');
                if (val.length < 3) {
                    showError(nameInput, errorSpan, 'Le nom doit contenir au moins 3 caractères.');
                    return false;
                } else {
                    clearError(nameInput, errorSpan);
                    return true;
                }
            }

            function validateDescription() {
                const val = descInput.value.trim();
                const errorSpan = document.getElementById('error-description');
                if (val.length < 10) {
                    showError(descInput, errorSpan, 'La description doit contenir au moins 10 caractères.');
                    return false;
                } else {
                    clearError(descInput, errorSpan);
                    return true;
                }
            }

            function validateIcon() {
                const val = iconInput.value.trim();
                const errorSpan = document.getElementById('error-icon');
                const preview = document.querySelector('#iconPreview i');

                // Update preview regardless of validation (best effort)
                if (val && (val.startsWith('fas fa-') || val.startsWith('far fa-') || val.startsWith('fab fa-'))) {
                    preview.className = val;
                } else if (preview) {
                    // Keep previous valid or default if empty/invalid? 
                    // Users might want to see it disappear if invalid.
                    // But for edit page, initial value is correct.
                    // If they clear it, show default.
                    if (!val) preview.className = 'fas fa-cube';
                }

                // Basic check: starts with fa, fas, far, fab
                if (!val.match(/^(fa|fas|far|fab)\s+fa-/)) {
                    showError(iconInput, errorSpan, "Format invalide (ex: 'fas fa-code').");
                    return false;
                } else {
                    clearError(iconInput, errorSpan);
                    return true;
                }
            }

            function validateImage() {
                const file = imageInput.files[0];
                const errorSpan = document.getElementById('error-image');
                // Image is optional for edit (can keep old image)

                if (file) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        showError(imageInput, errorSpan, 'Format non supporté (JPG, PNG, GIF, WEBP).');
                        errorSpan.style.display = 'block';
                        return false;
                    }
                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        showError(imageInput, errorSpan, 'L\'image est trop volumineuse (Max 5Mo).');
                        errorSpan.style.display = 'block';
                        return false;
                    }
                }
                clearError(imageInput, errorSpan);
                return true;
            }

            // Helpers
            function showError(input, span, msg) {
                input.classList.add('input-error');
                if (input.parentElement.classList.contains('input-wrapper') || input.parentElement.classList.contains('textarea-wrapper')) {
                    input.parentElement.style.borderColor = '#ef4444';
                }
                span.innerText = msg;
                span.style.display = 'block';
            }

            function clearError(input, span) {
                input.classList.remove('input-error');
                if (input.parentElement.classList.contains('input-wrapper') || input.parentElement.classList.contains('textarea-wrapper')) {
                    input.parentElement.style.borderColor = ''; // reset
                }
                span.style.display = 'none';
            }

            // Event Listeners
            nameInput.addEventListener('input', validateName);
            descInput.addEventListener('input', validateDescription);
            iconInput.addEventListener('input', validateIcon);
            imageInput.addEventListener('change', validateImage);

            form.addEventListener('submit', function (e) {
                let isValid = true;
                if (!validateName()) isValid = false;
                if (!validateDescription()) isValid = false;
                if (!validateIcon()) isValid = false;
                if (!validateImage()) isValid = false;

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

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
            // Clear error too
            const errorSpan = document.getElementById('error-image');
            errorSpan.style.display = 'none';
        }
    </script>

</body>

</html>