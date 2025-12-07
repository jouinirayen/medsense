<?php

class Utilisateur
{
    // Attributs privés
    private $id_utilisateur;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $dateNaissance;
    private $adresse;
    private $date_inscription;
    private $role;
    private $statut;
    private $photo_profil;
    private $diplome_path;
    private $diplome_statut;
    private $diplome_commentaire;
    private $diplome_date_verification;
    private $specialite;
    private $derniere_connexion;

    public function __construct($nom = "", $prenom = "", $email = "", $mot_de_passe = "", $dateNaissance = "", $adresse = "", $role = "patient", $statut = "actif", $diplome_path = null, $specialite = null)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        
        // Hasher le mot de passe seulement s'il n'est pas vide et n'est pas déjà hashé
        if (!empty($mot_de_passe)) {
            if (!preg_match('/^\$2y\$/', $mot_de_passe)) {
                $this->mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            } else {
                $this->mot_de_passe = $mot_de_passe;
            }
        } else {
            $this->mot_de_passe = $mot_de_passe;
        }
        
        $this->dateNaissance = $dateNaissance;
        $this->adresse = $adresse;
        $this->role = $role;
        $this->statut = $statut;
        $this->date_inscription = date('Y-m-d H:i:s');
        $this->photo_profil = null;
        $this->diplome_path = $diplome_path;
        $this->diplome_statut = 'en attente';
        $this->diplome_commentaire = null;
        $this->diplome_date_verification = null;
        $this->specialite = $specialite;
        $this->derniere_connexion = null;
    }

    // Getters
    public function getId() { return $this->id_utilisateur; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMotDePasse() { return $this->mot_de_passe; }
    public function getDateNaissance() { return $this->dateNaissance; }
    public function getAdresse() { return $this->adresse; }
    public function getDateInscription() { return $this->date_inscription; }
    public function getRole() { return $this->role; }
    public function getStatut() { return $this->statut; }
    public function getPhotoProfil() { return $this->photo_profil; }
    public function getDiplomePath() { return $this->diplome_path; }
    public function getDiplomeStatut() { return $this->diplome_statut; }
    public function getDiplomeCommentaire() { return $this->diplome_commentaire; }
    public function getDiplomeDateVerification() { return $this->diplome_date_verification; }
    public function getSpecialite() { return $this->specialite; }
    public function getDerniereConnexion() { return $this->derniere_connexion; }

    // Setters
    public function setId($id) { 
        $this->id_utilisateur = $id;
        return $this;
    }
    
    public function setNom($nom) { 
        $this->nom = $nom;
        return $this;
    }
    
    public function setPrenom($prenom) { 
        $this->prenom = $prenom;
        return $this;
    }
    
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }
    
    public function setMotDePasse($mot_de_passe) {
        if (!empty($mot_de_passe) && !preg_match('/^\$2y\$/', $mot_de_passe)) {
            $this->mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        } else {
            $this->mot_de_passe = $mot_de_passe;
        }
        return $this;
    }
    
    public function setDateNaissance($dateNaissance) {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }
    
    public function setAdresse($adresse) { 
        $this->adresse = $adresse;
        return $this;
    }
    
    public function setDateInscription($date) { 
        $this->date_inscription = $date;
        return $this;
    }
    
    public function setRole($role) {
        $this->role = $role;
        return $this;
    }
    
    public function setStatut($statut) {
        $this->statut = $statut;
        return $this;
    }
    
    public function setPhotoProfil($photo_profil) {
        $this->photo_profil = $photo_profil;
        return $this;
    }
    
    public function setDiplomePath($diplome_path) {
        $this->diplome_path = $diplome_path;
        return $this;
    }
    
    public function setDiplomeStatut($diplome_statut) {
        $this->diplome_statut = $diplome_statut;
        return $this;
    }
    
    public function setDiplomeCommentaire($diplome_commentaire) {
        $this->diplome_commentaire = $diplome_commentaire;
        return $this;
    }
    
    public function setDiplomeDateVerification($diplome_date_verification) {
        $this->diplome_date_verification = $diplome_date_verification;
        return $this;
    }
    
    public function setSpecialite($specialite) {
        $this->specialite = $specialite;
        return $this;
    }
    
    public function setDerniereConnexion($derniere_connexion) {
        $this->derniere_connexion = $derniere_connexion;
        return $this;
    }
    
    // Méthodes utilitaires
    public function estAdmin(): bool {
        return $this->role === 'admin';
    }
    
    public function estActif(): bool {
        return $this->statut === 'actif';
    }
    
    public function estMedecin(): bool {
        return $this->role === 'medecin';
    }
    
    public function estPatient(): bool {
        return $this->role === 'patient';
    }
    
    public function getNomComplet(): string {
        return $this->prenom . ' ' . $this->nom;
    }
    
    public function hasPhotoProfil(): bool {
        return !empty($this->photo_profil);
    }
    
    public function hasDiplome(): bool {
        return !empty($this->diplome_path);
    }
    
    public function estEnAttente(): bool {
        return $this->statut === 'en_attente';
    }
    
    public function estInactif(): bool {
        return $this->statut === 'inactif';
    }
    
    public function getAge(): ?int {
        if (empty($this->dateNaissance) || $this->dateNaissance === '0000-00-00') {
            return null;
        }
        
        try {
            $birthDate = new DateTime($this->dateNaissance);
            $today = new DateTime();
            
            if ($birthDate->format('Y') < 1900) {
                return null;
            }
            
            return $today->diff($birthDate)->y;
        } catch (Exception $e) {
            error_log("Erreur calcul âge: " . $e->getMessage());
            return null;
        }
    }
    
    public function getDateNaissanceFormatee(): ?string {
        if (empty($this->dateNaissance) || $this->dateNaissance === '0000-00-00') {
            return null;
        }
        
        try {
            $date = new DateTime($this->dateNaissance);
            return $date->format('d/m/Y');
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function getDateInscriptionFormatee(): string {
        try {
            $date = new DateTime($this->date_inscription);
            return $date->format('d/m/Y à H:i');
        } catch (Exception $e) {
            return $this->date_inscription;
        }
    }
    
    public function getDerniereConnexionFormatee(): ?string {
        if (empty($this->derniere_connexion)) {
            return null;
        }
        
        try {
            $date = new DateTime($this->derniere_connexion);
            return $date->format('d/m/Y à H:i');
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function validerMotDePasse($password): bool {
        return password_verify($password, $this->mot_de_passe);
    }
    
    public function getPhotoProfilUrl(): ?string {
        if (empty($this->photo_profil)) {
            return null;
        }
        
        if (strpos($this->photo_profil, 'http') === 0) {
            return $this->photo_profil;
        } else {
            return '/uploads/profiles/' . $this->photo_profil;
        }
    }
    
    public function getDiplomeUrl(): ?string {
        if (empty($this->diplome_path)) {
            return null;
        }
        
        return '/uploads/diplomes/' . $this->diplome_path;
    }
}