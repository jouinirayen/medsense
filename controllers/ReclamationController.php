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

        $errors = $this->validate($titre, $description);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_POST['titre'] = $titre;
            $_POST['description'] = $description;
            header('Location: ../../../views/reclamations/create.php');
            exit;
        }

        $this->reclamations->create([
            'titre' => $titre,
            'description' => $description,
            'date' => date('Y-m-d H:i:s'),
            'id_user' => $this->defaultUserId,
            'type' => Reclamation::TYPE_NORMAL,
            'statut' => Reclamation::STATUS_OPEN
        ]);

        $_SESSION['success_message'] = "Réclamation créée avec succès !";
        header('Location: ../../../views/reclamations/create.php');
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

        $errors = $this->validate($titre, $description, $id);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_POST['titre'] = $titre;
            $_POST['description'] = $description;
            header("Location: ../../../views/reclamations/edit.php?id=$id");
            exit;
        }

        $this->reclamations->update($id, $this->defaultUserId, [
            'titre' => $titre,
            'description' => $description
        ]);

        $_SESSION['success_message'] = "Réclamation mise à jour avec succès !";
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
            $this->reclamations->create([
                'titre' => "🚨 Urgence",
                'description' => "Alerte urgence envoyée par l'utilisateur",
                'date' => date('Y-m-d H:i:s'),
                'id_user' => $this->defaultUserId,
                'type' => Reclamation::TYPE_URGENCE,
                'statut' => Reclamation::STATUS_OPEN
            ]);

            $_SESSION['success_message'] = "Réclamation d'urgence envoyée !";
            header('Location: ../../../views/reclamations/urgence.php');
            exit;
        }

        $successMessage = $_SESSION['success_message'] ?? '';
        unset($_SESSION['success_message']);
        include '../../../views/reclamations/urgence.php';
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
