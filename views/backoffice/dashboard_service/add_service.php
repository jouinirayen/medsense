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
        $image = '';
        $imagePath = '';
        if (isset($_POST['generated_image_path']) && !empty($_POST['generated_image_path'])) {
            $imagePath = $_POST['generated_image_path'];
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "../../uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $imagePath = "uploads/" . basename($_FILES["image"]["name"]);
            }
        }

        $service = [
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'link' => '',
            'image' => $imagePath
        ];

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
    <link rel="preconnect" href="https://image.pollinations.ai" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/projet2025/views/frontoffice/page-accueil/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/projet2025/views/backoffice/dashboard_service/css/dashboard.css?v=<?php echo time(); ?>">
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

                <form method="POST" action="" class="modern-form" enctype="multipart/form-data" id="addServiceForm"
                    novalidate>

                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-tag"></i>
                            Nom du service <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="text" id="name" name="name" placeholder="Ex: Développement Web">
                        </div>
                        <span class="error-message" id="error-name">Le nom doit contenir au moins 3 caractères.</span>
                    </div>

                    <div class="form-group">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="description" style="margin-bottom: 0;">
                                <i class="fas fa-align-left"></i>
                                Description <span class="required">*</span>
                            </label>
                            <button type="button" id="btnGenerateAI" class="btn-secondary"
                                style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background-color: #ede9fe; color: #6366f1; border: 1px solid #c7d2fe;">
                                <i class="fas fa-magic"></i> Générer avec IA
                            </button>
                        </div>
                        <div class="textarea-wrapper">
                            <i class="fas fa-align-left textarea-icon"></i>
                            <textarea id="description" name="description" rows="5"
                                placeholder="Décrivez le service en détail..."></textarea>
                        </div>
                        <span class="error-message" id="error-description">La description doit contenir au moins 10
                            caractères.</span>
                    </div>



                    <div class="form-group">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="icon" style="margin-bottom: 0;">
                                <i class="fas fa-icons"></i>
                                Icône Font Awesome <span class="required">*</span>
                            </label>
                            <button type="button" id="btnGenerateIcon" class="btn-secondary"
                                style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background-color: #ecfccb; color: #4d7c0f; border: 1px solid #bef264;">
                                <i class="fas fa-magic"></i> Auto Icon
                            </button>
                        </div>
                        <div class="input-wrapper" style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-icons input-icon"></i>
                            <input type="text" id="icon" name="icon" placeholder="Ex: fas fa-code" style="flex: 1;">
                            <div id="iconPreview"
                                style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 8px; border: 1px solid #e2e8f0; color: #3b82f6;">
                                <i class="fas fa-cube"></i>
                            </div>
                        </div>
                        <span class="error-message" id="error-icon">Format invalide (doit commencer par 'fas fa-', 'far
                            fa-' ou 'fab fa-').</span>
                    </div>

                    <input type="hidden" name="generated_image_path" id="generated_image_path">

                    <div class="form-group">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="image" style="margin-bottom: 0;">
                                <i class="fas fa-image"></i>
                                Image de fond
                            </label>
                            <button type="button" id="btnGenerateImage" class="btn-secondary"
                                style="padding: 0.25rem 0.75rem; font-size: 0.85rem; background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">
                                <i class="fas fa-magic"></i> Génération Image IA
                            </button>
                        </div>
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
                        <span class="error-message" id="error-image">Veuillez sélectionner une image valide (JPG,
                            PNG).</span>

                    </div>



                    <div class="form-actions">
                        <button type="submit" name="Save" class="btn-primary btn-submit">
                            <i class="fas fa-plus"></i>
                            <span>Ajouter le service</span>
                        </button>
                        <a href="../dashboard.php" class="btn-secondary">
                            <i class="fas fa-times"></i>
                            <span>Annuler</span>
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('addServiceForm');
            const nameInput = document.getElementById('name');
            const descInput = document.getElementById('description');
            const iconInput = document.getElementById('icon');
            const imageInput = document.getElementById('image');

            // --- AI Description Logic ---
            const btnDescAI = document.getElementById('btnGenerateAI');
            if (btnDescAI) {
                btnDescAI.addEventListener('click', function () {
                    const nameVal = document.getElementById('name').value.trim();
                    const descField = document.getElementById('description');
                    const btn = this;

                    if (nameVal.length < 3) {
                        alert("Veuillez d'abord entrer un nom de service valide (3 caractères min).");
                        return;
                    }

                    // UI Loading
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';

                    fetch('generate_description_endpoint.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ service_name: nameVal })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                descField.value = data.description;
                                // Trigger validation manually
                                descField.dispatchEvent(new Event('input'));
                            } else {
                                alert("Erreur IA : " + data.error);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert("Une erreur est survenue.");
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        });
                });
            }



            // --- Auto Icon Logic ---
            const btnGenIcon = document.getElementById('btnGenerateIcon');
            if (btnGenIcon) {
                btnGenIcon.addEventListener('click', function () {
                    const nameVal = document.getElementById('name').value.trim();
                    const iconInput = document.getElementById('icon');
                    const btn = this;

                    if (nameVal.length < 3) {
                        alert("Veuillez d'abord entrer un nom de service valide.");
                        return;
                    }

                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';

                    fetch('generate_icon_endpoint.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ service_name: nameVal })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                iconInput.value = data.icon;
                                // Trigger input event to update visual preview
                                iconInput.dispatchEvent(new Event('input'));
                            } else {
                                alert("Erreur: " + data.error);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert("Erreur réseau.");
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        });
                });
            }




            // --- Async Fast Image Generation Logic ---
            const btnGenImage = document.getElementById('btnGenerateImage');
            const hiddenImgPath = document.getElementById('generated_image_path');

            if (btnGenImage) {
                // Store original text
                btnGenImage.setAttribute('data-original-text', btnGenImage.innerHTML);

                btnGenImage.addEventListener('click', function () {
                    const nameVal = document.getElementById('name').value.trim();
                    if (nameVal.length < 3) {
                        alert("Veuillez d'abord entrer un nom de service valide.");
                        return;
                    }

                    const originalText = btnGenImage.getAttribute('data-original-text');

                    function resetBtn(msg = null) {
                        btnGenImage.disabled = false;
                        btnGenImage.innerHTML = originalText;
                        if (msg) alert(msg);
                    }

                    btnGenImage.disabled = true;
                    btnGenImage.disabled = true;
                    btnGenImage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';

                    // 1. Generate Prompt (LLM for 100% Relevance)

                    fetch('generate_prompt_endpoint.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ service_name: nameVal })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) throw new Error(data.error);

                            const prompt = data.prompt;


                            // 2. Construct URL
                            const encodedPrompt = encodeURIComponent(prompt);
                            const seed = Math.floor(Math.random() * 1000000);
                            const imageUrl = `https://image.pollinations.ai/prompt/${encodedPrompt}?width=768&height=512&nologo=true&seed=${seed}&model=flux`;

                            // 3. Load Image
                            const previewImg = document.getElementById('previewImage');
                            const tmpImg = new Image();

                            // Timeout safety (15s)
                            const timeoutId = setTimeout(() => {
                                if (btnGenImage.disabled) resetBtn("Délai d'attente dépassé (Timeout). Réessayez.");
                            }, 15000);

                            tmpImg.onload = function () {
                                clearTimeout(timeoutId);
                                previewImg.src = imageUrl;
                                document.getElementById('fileUploadArea').style.display = 'none';
                                document.getElementById('filePreview').style.display = 'block';
                                document.getElementById('image').value = '';
                                const errImg = document.getElementById('error-image');
                                if (errImg) errImg.style.display = 'none';

                                // 4. Background Save
                                btnGenImage.innerHTML = '<i class="fas fa-save"></i> Enregistrement...';

                                fetch('save_image_url_endpoint.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ url: imageUrl })
                                })
                                    .then(r => r.json())
                                    .then(saveData => {
                                        if (saveData.success) {
                                            document.getElementById('generated_image_path').value = saveData.path;
                                            btnGenImage.innerHTML = '<i class="fas fa-check"></i> Image Prête';
                                            setTimeout(() => {
                                                btnGenImage.disabled = false;
                                                btnGenImage.innerHTML = originalText;
                                            }, 2000);
                                        } else {
                                            resetBtn("Erreur de sauvegarde sur le serveur.");
                                        }
                                    })
                                    .catch(e => {
                                        console.error(e);
                                        resetBtn("Erreur réseau lors de la sauvegarde.");
                                    });
                            };

                            tmpImg.onerror = function () {
                                clearTimeout(timeoutId);
                                console.error("Pollinations API Error");
                                resetBtn("Impossible de générer l'image (API Erreur). Réessayez.");
                            };

                            tmpImg.src = imageUrl;
                        })
                        .catch(e => {
                            console.error(e);
                            resetBtn("Erreur lors de la génération du prompt (IA).");
                        });


                });
            }

            // ------------------------------------------




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
                } else {
                    preview.className = 'fas fa-cube'; // default
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
                // Image is optional for add? If strictly required, check file existence.
                // Assuming optional based on PHP code dealing with empty image, BUT best practice is specific checking.
                // Let's validate only if file is selected, or if user wants it mandatory (User said "control de saisie specifique", usually implies strictness).
                // Let's keep it optional but validate type if selected.

                if (file) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        showError(imageInput, errorSpan, 'Format non supporté (JPG, PNG, GIF, WEBP).');
                        // Custom logic for file input styling might be tricky with hidden input, 
                        // so we target the wrapper or just show the text.
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
                // Also trigger validation
                // validateImage() is attached to change event, so it runs.

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