<?php


include __DIR__ . '/../controllers/ServiceController.php';

$serviceController = new ServiceController();
$serviceController->gererModification();

// Get all services for the table
$services = $serviceController->obtenirTousLesServices();


$serviceToEdit = null;
if (isset($_GET['edit_id'])) {
    $serviceToEdit = $serviceController->obtenirServiceParId($_GET['edit_id']);
}

$pageTitle = 'Modifier un Service';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <img src="../uploads/logo.jpeg" alt="Logo Medsense" style="height: 125px; width: auto;">
        </div>
        <nav class="nav-links">
            <a href="../index.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-home"></i></span>
                <span>Front Office</span>
            </a>
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="add_service.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-plus"></i></span>
                <span>Ajouter</span>
            </a>
            <a href="list_services.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-list"></i></span>
                <span>Liste</span>
            </a>
            <a href="edit_service.php" class="nav-link active">
                <span class="nav-icon"><i class="fas fa-edit"></i></span>
                <span>Modifier</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <?php if ($serviceToEdit): ?>
        <section class="hero-section">
            <h1 class="hero-title">Modifier un service</h1>
            <p class="hero-description">
                Modifiez les informations du service ci-dessous.
            </p>
        </section>

        <section class="form-section">
            <div class="form-container">
            <form method="POST" action="edit_service.php">
                <input type="hidden" name="edit_service" value="1">
                <input type="hidden" name="id" value="<?php echo $serviceToEdit['id']; ?>">
                
                <div class="form-group">
                    <label for="name">Nom du service *</label>
                    <input type="text" id="name" name="name" value="<?php echo $serviceToEdit['name']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4"><?php echo $serviceToEdit['description']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="icon">Icône Font Awesome *</label>
                    <input type="text" id="icon" name="icon" value="<?php echo $serviceToEdit['icon']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="link">Lien de la page *</label>
                    <input type="text" id="link" name="link" value="<?php echo $serviceToEdit['link']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Image de fond (chemin ou URL)</label>
                    <input type="text" id="image" name="image" value="<?php echo $serviceToEdit['image']; ?>">
                    <small>Ex: uploads/mon_image.jpg ou une URL complète. Laissez vide pour conserver l'image actuelle.</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="edit_service.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
            </div>
        </section>
    <?php else: ?>
        <section class="hero-section">
            <h1 class="hero-title">Modifier un service</h1>
            <p class="hero-description">
                Sélectionnez un service dans la liste ci-dessous pour le modifier.
            </p>
        </section>

        <section class="table-section">
            <div class="table-container">
            <?php if (empty($services)): ?>
                <p>Aucun service disponible. <a href="add_service.php">Ajoutez un service</a> d'abord.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Icon</th>
                            <th>Link</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?php echo $service['id']; ?></td>
                                <td><?php echo $service['name']; ?></td>
                                <td><?php echo substr($service['description'], 0, 50) . '...'; ?></td>
                                <td><i class="<?php echo $service['icon']; ?>"></i></td>
                                <td><?php echo $service['link']; ?></td>
                                <td>
                                    <?php if (!empty($service['image'])): ?>
                                        <img src="../<?php echo $service['image']; ?>" alt="Image" style="max-width: 80px; max-height: 60px; border-radius: 4px; object-fit: cover;">
                                    <?php else: ?>
                                        <span style="color: #999;">Aucune image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_service.php?edit_id=<?php echo $service['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

</body>
</html>