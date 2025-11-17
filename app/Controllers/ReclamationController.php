<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Reclamation;
use App\Models\Response;

class ReclamationController extends Controller
{
    private Reclamation $reclamations;
    private Response $responses;

    public function __construct()
    {
        Auth::requireLogin();
        $this->reclamations = new Reclamation();
        $this->responses = new Response();
    }

    public function index(): void
    {
        $userId = Auth::id();
        $reclamations = $this->reclamations->forUser($userId);

        $this->render('reclamations/index', [
            'reclamations' => $reclamations,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    public function create(): void
    {
        $this->render('reclamations/create', [
            'errors' => Session::flash('errors') ?? []
        ]);
    }

    public function store(): void
    {
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = $this->validate($titre, $description);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            $this->redirect(route('reclamation', 'create'));
        }

        $this->reclamations->create([
            'titre' => $titre,
            'description' => $description,
            'date' => date('Y-m-d H:i:s'),
            'id_user' => Auth::id(),
            'type' => TYPE_NORMAL,
            'statut' => STATUS_OPEN
        ]);

        Session::flash('success', "Votre réclamation a été créée avec succès!");
        $this->redirect(route('reclamation'));
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $userId = Auth::id();
        $reclamation = $this->reclamations->findForUser($id, $userId);

        if (!$reclamation) {
            Session::flash('error', "Réclamation introuvable.");
            $this->redirect(route('reclamation'));
        }

        $responses = $this->responses->forReclamation($id);

        $this->render('reclamations/show', [
            'reclamation' => $reclamation,
            'responses' => $responses
        ]);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $userId = Auth::id();
        $reclamation = $this->reclamations->findForUser($id, $userId);

        if (!$reclamation) {
            Session::flash('error', "Réclamation introuvable.");
            $this->redirect(route('reclamation'));
        }

        $this->render('reclamations/edit', [
            'reclamation' => $reclamation,
            'errors' => Session::flash('errors') ?? []
        ]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = $this->validate($titre, $description, $id);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            $this->redirect(route('reclamation', 'edit', ['id' => $id]));
        }

        $userId = Auth::id();
        $reclamation = $this->reclamations->findForUser($id, $userId);

        if (!$reclamation) {
            Session::flash('error', "Réclamation introuvable.");
            $this->redirect(route('reclamation'));
        }

        $this->reclamations->update($id, $userId, [
            'titre' => $titre,
            'description' => $description
        ]);

        Session::flash('success', "Réclamation mise à jour avec succès!");
        $this->redirect(route('reclamation'));
    }

    public function destroy(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $userId = Auth::id();

        $reclamation = $this->reclamations->findForUser($id, $userId);

        if (!$reclamation) {
            Session::flash('error', "Réclamation introuvable.");
            $this->redirect(route('reclamation'));
        }

        $this->responses->deleteForReclamation($id);
        $this->reclamations->deleteForUser($id, $userId);

        Session::flash('success', "Réclamation supprimée avec succès!");
        $this->redirect(route('reclamation'));
    }

    public function urgence(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->reclamations->create([
                'titre' => "🚨 Urgence",
                'description' => "Alerte urgence envoyée par l'utilisateur",
                'date' => date('Y-m-d H:i:s'),
                'id_user' => Auth::id(),
                'type' => TYPE_URGENCE,
                'statut' => STATUS_OPEN
            ]);

            Session::flash('success', "⚠️ ALERTE URGENCE ENVOYÉE! Les administrateurs ont été notifiés.");
            $this->redirect(route('reclamation', 'urgence'));
        }

        $this->render('reclamations/urgence', [
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    private function validate(string $titre, string $description, ?int $id = null): array
    {
        $errors = [];

        if ($id !== null && $id <= 0) {
            $errors[] = "ID réclamation invalide.";
        }
        if (empty($titre)) {
            $errors[] = "Le titre est requis.";
        } elseif (strlen($titre) > 255) {
            $errors[] = "Le titre ne doit pas dépasser 255 caractères.";
        }
        if (empty($description)) {
            $errors[] = "La description est requise.";
        } elseif (strlen($description) < 10) {
            $errors[] = "La description doit contenir au moins 10 caractères.";
        }

        return $errors;
    }
}

