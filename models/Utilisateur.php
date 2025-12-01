<?php

class Utilisateur
{
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
    private $reset_token;
    private $reset_token_expires;
    private $photo_profil;

    public function __construct($nom = "", $prenom = "", $email = "", $mot_de_passe = "", $dateNaissance = "", $adresse = "", $role = "utilisateur", $statut = "actif")
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->setEmail($email);
        $this->setMotDePasse($mot_de_passe);
        $this->setDateNaissance($dateNaissance);
        $this->adresse = $adresse;
        $this->setRole($role);
        $this->setStatut($statut);
        $this->date_inscription = date('Y-m-d H:i:s');
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
    public function getResetToken() { return $this->reset_token; }
    public function getResetTokenExpires() { return $this->reset_token_expires; }
    public function getPhotoProfil() { return $this->photo_profil; }

    // Setters avec validation
    public function setId($id) { 
        if ($id > 0) {
            $this->id_utilisateur = $id;
        }
        return $this;
    }
    
    public function setNom($nom) { 
        if (!empty(trim($nom))) {
            $this->nom = htmlspecialchars(trim($nom));
        }
        return $this;
    }
    
    public function setPrenom($prenom) { 
        if (!empty(trim($prenom))) {
            $this->prenom = htmlspecialchars(trim($prenom));
        }
        return $this;
    }
    
    public function setEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
        } else {
            throw new InvalidArgumentException("Email invalide");
        }
        return $this;
    }
    
    public function setMotDePasse($mot_de_passe) {
        if (!empty($mot_de_passe)) {
            // Si le mot de passe n'est pas déjà hashé (longueur < 60)
            if (strlen($mot_de_passe) < 60) {
                $this->mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            } else {
                $this->mot_de_passe = $mot_de_passe;
            }
        }
        return $this;
    }
    
    public function setDateNaissance($dateNaissance) {
        if ($this->validateDate($dateNaissance)) {
            $this->dateNaissance = $dateNaissance;
        }
        return $this;
    }
    
    public function setAdresse($adresse) { 
        $this->adresse = htmlspecialchars(trim($adresse));
        return $this;
    }
    
    public function setDateInscription($date) { 
        if ($this->validateDate($date, 'Y-m-d H:i:s')) {
            $this->date_inscription = $date;
        }
        return $this;
    }
    
    public function setRole($role) {
        $rolesValides = ['utilisateur', 'admin', 'medecin'];
        if (in_array($role, $rolesValides)) {
            $this->role = $role;
        }
        return $this;
    }
    
    public function setStatut($statut) {
        $statutsValides = ['actif', 'inactif', 'suspendu'];
        if (in_array($statut, $statutsValides)) {
            $this->statut = $statut;
        }
        return $this;
    }

    public function setResetToken($token) {
        $this->reset_token = $token;
        return $this;
    }

    public function setResetTokenExpires($expires) {
        $this->reset_token_expires = $expires;
        return $this;
    }

    public function setPhotoProfil($photo) {
        $this->photo_profil = $photo;
        return $this;
    }

    // Méthodes métier
    public function verifyMotDePasse($mot_de_passe) {
        return password_verify($mot_de_passe, $this->mot_de_passe);
    }

    public function getAge() {
        if ($this->dateNaissance) {
            $today = new DateTime();
            $birthdate = new DateTime($this->dateNaissance);
            return $birthdate->diff($today)->y;
        }
        return null;
    }

    public function estActif() {
        return $this->statut === 'actif';
    }

    public function estAdmin() {
        return $this->role === 'admin';
    }

    public function estMedicin() {
        return $this->role === 'medecin';
    }

    public function genererResetToken() {
        $token = bin2hex(random_bytes(32));
        $this->reset_token = $token;
        $this->reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        return $token;
    }

    public function resetTokenEstValide() {
        if (!$this->reset_token || !$this->reset_token_expires) {
            return false;
        }
        return new DateTime() < new DateTime($this->reset_token_expires);
    }

    // Méthode utilitaire
    private function validateDate($date, $format = 'Y-m-d') {
        if (empty($date)) return true;
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function toArray() {
        return [
            'id_utilisateur' => $this->id_utilisateur,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'dateNaissance' => $this->dateNaissance,
            'adresse' => $this->adresse,
            'date_inscription' => $this->date_inscription,
            'role' => $this->role,
            'statut' => $this->statut,
            'reset_token' => $this->reset_token,
            'reset_token_expires' => $this->reset_token_expires,
            'photo_profil' => $this->photo_profil,
            'age' => $this->getAge()
        ];
    }

    public function __toString() {
        return $this->prenom . ' ' . $this->nom . ' (' . $this->email . ')';
    }
}
?>