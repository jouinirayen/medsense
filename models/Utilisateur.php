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

    public function __construct($nom = "", $prenom = "", $email = "", $mot_de_passe = "", $dateNaissance = "", $adresse = "", $role = "utilisateur", $statut = "actif")
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->dateNaissance = $dateNaissance;
        $this->adresse = $adresse;
        $this->role = $role;
        $this->statut = $statut;
        $this->date_inscription = date('Y-m-d H:i:s');
    }
 


    
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

    
    public function setId($id) { $this->id_utilisateur = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePasse($mot_de_passe) { $this->mot_de_passe = $mot_de_passe; }
    public function setDateNaissance($dateNaissance) { $this->dateNaissance = $dateNaissance; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }
    public function setDateInscription($date) { $this->date_inscription = $date; }
    public function setRole($role) { $this->role = $role; }
    public function setStatut($statut) { $this->statut = $statut; }

   
    public function hashMotDePasse($mot_de_passe) {
        return password_hash($mot_de_passe, PASSWORD_DEFAULT);
    }

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
}
?>