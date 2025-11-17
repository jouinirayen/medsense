<?php

include __DIR__ . '/../controllers/ServiceController.php';

$serviceController = new ServiceController();


$services = $serviceController->obtenirTousLesServices();


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
            <a href="edit_service.php" class="nav-link">
                <span class="nav-icon"><i class="fas fa-edit"></i></span>
                <span>Modifier</span>
            </a>
        </nav>
    </header>

<main class="main-content">
    <section class="hero-section">
        <h1 class="hero-title">Supprimer un service</h1>
        <p class="hero-description">
            Sélectionnez un service dans la liste ci-dessous pour le supprimer. Cette action est irréversible.
        </p>
    </section>

    <section class="table-section">
        <div class="table-container">
        <?php if (empty($services)): ?>
            <p>Aucun service disponible à supprimer.</p>
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
                                <a href="confirm_delete.php?id=<?php echo $service['id']; ?>" 
                                   class="btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>