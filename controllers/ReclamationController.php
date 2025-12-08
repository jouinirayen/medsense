<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

class ReclamationController
{
    private Reclamation $reclamations;
    private Response $responses;
    private int $defaultUserId = 1; // Hardcoded user ID

    public function __construct()
    {
        $this->reclamations = new Reclamation();
        $this->responses = new Response();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // List all reclamations
    public function index(): void
    {
        $reclamations = $this->reclamations->forUser($this->defaultUserId);
        include '../../../views/reclamations/index.php';
    }

    // Show create form
    public function create(): void
    {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $successMessage = $_SESSION['success_message'] ?? '';
        unset($_SESSION['success_message']);
        include '../../../views/reclamations/create.php';
    }

    // Store new reclamation
    public function store(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../../../views/reclamations/create.php');
        exit;
    }

    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // FONCTIONNALITÉ 2: Détection de mauvais mots
    $reclamation = new Reclamation();
    $badWords = $reclamation->detectBadWords($titre, $description);
    if ($badWords !== null) {
        $_SESSION['errors'] = [
            "⚠️ ATTENTION : Votre réclamation contient des mots inappropriés.",
            "Veuillez reformuler votre message de manière respectueuse.",
            "Mots détectés : " . implode(', ', $badWords)
        ];
        $_SESSION['old_titre'] = $titre;
        $_SESSION['old_description'] = $description;
        header('Location: ../../../views/frontoffice/reclamation/create.php');
        exit;
    }

    $errors = $this->validate($titre, $description);
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_titre'] = $titre;
        $_SESSION['old_description'] = $description;
        header('Location: ../../../views/frontoffice/reclamation/create.php');
        exit;
    }

    // FONCTIONNALITÉ 1: Génération automatique de description détaillée
    $detailedDescription = $reclamation->generateDetailedDescription($titre, $description);
    
    $reclamation->setTitre($titre)
                ->setDescription($detailedDescription)
                ->setDate(date('Y-m-d H:i:s'))
                ->setUserId($this->defaultUserId)
                ->setType(Reclamation::TYPE_NORMAL)
                ->setStatut(Reclamation::STATUS_OPEN);
    
    if ($reclamation->create()) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Réclamation créée avec succès ! Description enrichie automatiquement.",
            'show' => true
        ];
    } else {
        $_SESSION['errors'] = ["Erreur lors de la création de la réclamation"];
    }
    
    header('Location: ../../../views/frontoffice/reclamation/index.php');
    exit;
}

    // Show single reclamation
    public function show(int $id): void
    {
        $reclamation = $this->reclamations->findForUser($id, $this->defaultUserId);
        if (!$reclamation) {
            echo "Réclamation introuvable.";
            exit;
        }

        $responses = $this->responses->forReclamation($id);
        include '../../../views/reclamations/show.php';
    }

    // Show edit form
    public function edit(int $id): void
    {
        $reclamation = $this->reclamations->findForUser($id, $this->defaultUserId);
        if (!$reclamation) {
            echo "Réclamation introuvable.";
            exit;
        }

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        include '../../../views/reclamations/edit.php';
    }

    // Update reclamation
   public function update(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../../../views/reclamations/index.php');
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // FONCTIONNALITÉ 2: Détection de mauvais mots
    $reclamation = new Reclamation();
    $badWords = $reclamation->detectBadWords($titre, $description);
    if ($badWords !== null) {
        $_SESSION['errors'] = [
            "⚠️ ATTENTION : Votre réclamation contient des mots inappropriés.",
            "Veuillez reformuler votre message de manière respectueuse.",
            "Mots détectés : " . implode(', ', $badWords)
        ];
        header("Location: ../../../views/reclamations/edit.php?id=$id");
        exit;
    }

    $errors = $this->validate($titre, $description, $id);
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../../../views/reclamations/edit.php?id=$id");
        exit;
    }

    $reclamation = $this->reclamations->findForUser($id, $this->defaultUserId);
    if (!$reclamation) {
        $_SESSION['errors'] = ["Réclamation introuvable"];
        header('Location: ../../../views/reclamations/index.php');
        exit;
    }

    // FONCTIONNALITÉ 1: Génération automatique de description détaillée
    $detailedDescription = $reclamation->generateDetailedDescription($titre, $description);

    $reclamation->setTitre($titre)
                ->setDescription($detailedDescription);
    
    if ($reclamation->update()) {
        $_SESSION['success_message'] = "Réclamation mise à jour avec succès ! Description enrichie automatiquement.";
    } else {
        $_SESSION['errors'] = ["Erreur lors de la mise à jour"];
    }
    
    header('Location: ../../../views/reclamations/index.php');
    exit;
}

    // Delete a reclamation
    public function destroy(int $id): void
    {
        $reclamation = $this->reclamations->findForUser($id, $this->defaultUserId);
        if (!$reclamation) {
            echo "Réclamation introuvable.";
            exit;
        }

        $this->responses->deleteForReclamation($id);
        $this->reclamations->deleteForUser($id, $this->defaultUserId);

        $_SESSION['success_message'] = "Réclamation supprimée avec succès !";
        header('Location: ../../../views/reclamations/index.php');
        exit;
    }

    // Urgence reclamation
   public function urgence(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reclamation = new Reclamation();
        
        // Note: Pour les urgences, on garde le message simple mais on peut enrichir
        $titre = "🚨 Urgence";
        $descriptionBase = "Alerte urgence envoyée par l'utilisateur";
        
        // FONCTIONNALITÉ 1: Génération automatique de description détaillée pour urgence
        $detailedDescription = $reclamation->generateDetailedDescription($titre, $descriptionBase);
        
        $reclamation->setTitre($titre)
                    ->setDescription($detailedDescription)
                    ->setDate(date('Y-m-d H:i:s'))
                    ->setUserId($this->defaultUserId)
                    ->setType(Reclamation::TYPE_URGENCE)
                    ->setStatut(Reclamation::STATUS_OPEN);
        
        if ($reclamation->create()) {
            $_SESSION['success_message'] = "Réclamation d'urgence envoyée !";
        } else {
            $_SESSION['errors'] = ["Erreur lors de l'envoi de l'urgence"];
        }
        
        header('Location: ../../../views/reclamations/urgence.php');
        exit;
    }

    $successMessage = $_SESSION['success_message'] ?? '';
    unset($_SESSION['success_message']);
    include '../../../views/reclamations/urgence.php';
}

    // FONCTIONNALITÉ 3: Afficher les statistiques
    public function statistics(): void
    {
        $reclamation = new Reclamation();
        $stats = $reclamation->getStatistics();
        include '../../../views/backoffice/reponse/admin_statistics.php';
    }

    // Validation function
    private function validate(string $titre, string $description, ?int $id = null): array
    {
        $errors = [];
        if ($id !== null && $id <= 0) $errors[] = "ID réclamation invalide.";
        if (empty($titre)) $errors[] = "Le titre est requis.";
        elseif (strlen($titre) < 3) $errors[] = "Le titre doit contenir au moins 3 caractères.";
        elseif (strlen($titre) > 255) $errors[] = "Le titre ne doit pas dépasser 255 caractères.";
        if (empty($description)) $errors[] = "La description est requise.";
        elseif (strlen($description) < 10) $errors[] = "La description doit contenir au moins 10 caractères.";
        elseif (strlen($description) > 5000) $errors[] = "La description ne doit pas dépasser 5000 caractères.";
        return $errors;
    }
}
