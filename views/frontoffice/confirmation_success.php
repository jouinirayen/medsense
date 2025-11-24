<?php
$serviceName = $_GET['service'] ?? 'Votre service';
$serviceDescription = $_GET['description'] ?? '';
$appointmentDate = $_GET['date'] ?? '';
$appointmentTime = $_GET['time'] ?? '';
$emailAddress = $_GET['email'] ?? '';

$humanReadableDate = $appointmentDate ? date('l, F j, Y', strtotime($appointmentDate)) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/confirm-appointment.css">
</head>
<body>
    <main class="confirm-page">
        <header class="confirm-header">
            <a href="front.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Retour à l'accueil</span>
            </a>
        </header>

        <section class="details-card" style="text-align:center;">
            <div class="card-heading" style="justify-content:center;">
                <div class="card-heading-icon" style="background:linear-gradient(135deg,#34d399,#059669);">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <p class="label">Confirmation</p>
                    <h1 style="font-size:28px;margin-top:6px;color:#0f172a;">Votre rendez-vous est confirmé</h1>
                </div>
            </div>

            <div class="detail-block" style="margin-top:32px;">
                <div class="detail-item" style="justify-content:center; text-align:left;">
                    <div class="detail-icon service" style="align-self:flex-start;">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div>
                        <p class="detail-label">Service</p>
                        <p class="detail-title"><?php echo $serviceName; ?></p>
                        <?php if (!empty($serviceDescription)): ?>
                            <p class="detail-description"><?php echo $serviceDescription; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($appointmentDate): ?>
                <div class="detail-item" style="justify-content:center; text-align:left;">
                    <div class="detail-icon date">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <p class="detail-label">Date</p>
                        <p class="detail-title"><?php echo $humanReadableDate; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($appointmentTime): ?>
                <div class="detail-item" style="justify-content:center; text-align:left;">
                    <div class="detail-icon time">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="detail-label">Heure</p>
                        <p class="detail-title"><?php echo $appointmentTime; ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($emailAddress): ?>
                <div class="detail-item" style="justify-content:center; text-align:left;">
                    <div class="detail-icon doctor" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p class="detail-label">Confirmation envoyée à</p>
                        <p class="detail-title"><?php echo $emailAddress; ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="actions" style="justify-content:center;margin-top:24px;">
                <a href="front.php" class="btn primary" style="text-decoration:none;">
                    <i class="fas fa-home"></i>
                    <span>Retour à l'accueil</span>
                </a>
            </div>
        </section>
    </main>
</body>
</html>

