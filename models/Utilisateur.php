<?php

class Utilisateur
{
    private $id_utilisateur;
    private $username;
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
    private $diplome_path;
    private $historique_connexions;
    private $diplome_statut;
    private $diplome_commentaire;
    private $diplome_date_verification;
    private $derniere_connexion;
    private $bio;
    private $idService;
    private $heure1_debut;
    private $heure1_fin;
    private $heure2_debut;
    private $heure2_fin;
    private $heure3_debut;
    private $heure3_fin;
    private $heure4_debut;
    private $heure4_fin;
    private $image;
    private $note_globale;
    private $nb_avis;
    private $langues;
    private $prix_consultation;
    private $experience;
    private $specialite;

    public function __construct(
        $nom = "", 
        $prenom = "", 
        $email = "", 
        $mot_de_passe = "", 
        $dateNaissance = "", 
        $adresse = "", 
        $role = "patient", 
        $statut = "en_attente", 
        $diplome_path = null, 
        $bio = null,
        $idService = null,
        $heure1_debut = null,
        $heure1_fin = null,
        $heure2_debut = null,
        $heure2_fin = null,
        $heure3_debut = null,
        $heure3_fin = null,
        $heure4_debut = null,
        $heure4_fin = null,
        $image = null,
        $note_globale = 0,
        $nb_avis = 0,
        $langues = null,
        $prix_consultation = null,
        $experience = null,
        $username = null,
        $specialite = null
    ) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->username = $username;
        
        if (!empty($mot_de_passe)) {
            if (!preg_match('/^\$2[ayb]\$.{56}$/', $mot_de_passe)) {
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
        $this->reset_token = null;
        $this->reset_token_expires = null;
        $this->diplome_path = $diplome_path;
        $this->historique_connexions = null;
        $this->diplome_statut = 'en attente';
        $this->diplome_commentaire = null;
        $this->diplome_date_verification = null;
        $this->derniere_connexion = null;
        $this->bio = $bio;
        $this->idService = $idService;
        $this->heure1_debut = $heure1_debut;
        $this->heure1_fin = $heure1_fin;
        $this->heure2_debut = $heure2_debut;
        $this->heure2_fin = $heure2_fin;
        $this->heure3_debut = $heure3_debut;
        $this->heure3_fin = $heure3_fin;
        $this->heure4_debut = $heure4_debut;
        $this->heure4_fin = $heure4_fin;
        $this->image = $image;
        $this->note_globale = $note_globale;
        $this->nb_avis = $nb_avis;
        $this->langues = $langues;
        $this->prix_consultation = $prix_consultation;
        $this->experience = $experience;
        $this->specialite = $specialite;
    }


    public function getId() { return $this->id_utilisateur; }
    public function getUsername() { return $this->username; }
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
    public function getDiplomePath() { return $this->diplome_path; }
    public function getHistoriqueConnexions() { return $this->historique_connexions; }
    public function getDiplomeStatut() { return $this->diplome_statut; }
    public function getDiplomeCommentaire() { return $this->diplome_commentaire; }
    public function getDiplomeDateVerification() { return $this->diplome_date_verification; }
    public function getDerniereConnexion() { return $this->derniere_connexion; }
    public function getBio() { return $this->bio; }
    public function getIdService() { return $this->idService; }
    public function getHeure1Debut() { return $this->heure1_debut; }
    public function getHeure1Fin() { return $this->heure1_fin; }
    public function getHeure2Debut() { return $this->heure2_debut; }
    public function getHeure2Fin() { return $this->heure2_fin; }
    public function getHeure3Debut() { return $this->heure3_debut; }
    public function getHeure3Fin() { return $this->heure3_fin; }
    public function getHeure4Debut() { return $this->heure4_debut; }
    public function getHeure4Fin() { return $this->heure4_fin; }
    public function getImage() { return $this->image; }
    public function getNoteGlobale() { return $this->note_globale; }
    public function getNbAvis() { return $this->nb_avis; }
    public function getLangues() { return $this->langues; }
    public function getPrixConsultation() { return $this->prix_consultation; }
    public function getExperience() { return $this->experience; }
    public function getSpecialite() { return $this->specialite; }

    public function setId($id) { $this->id_utilisateur = (int)$id; return $this; }
    public function setUsername($username) { $this->username = htmlspecialchars(trim($username), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setNom($nom) { $this->nom = htmlspecialchars(trim($nom), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setPrenom($prenom) { $this->prenom = htmlspecialchars(trim($prenom), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setEmail($email) { if(filter_var($email, FILTER_VALIDATE_EMAIL)) $this->email = strtolower(trim($email)); return $this; }
    public function setMotDePasse($mot_de_passe, $hashed = false) { 
        $this->mot_de_passe = (!$hashed && !empty($mot_de_passe)) ? password_hash($mot_de_passe, PASSWORD_DEFAULT) : $mot_de_passe; 
        return $this; 
    }
    public function setDateNaissance($dateNaissance) { if(DateTime::createFromFormat('Y-m-d', $dateNaissance) !== false) $this->dateNaissance = $dateNaissance; return $this; }
    public function setAdresse($adresse) { $this->adresse = htmlspecialchars(trim($adresse), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setDateInscription($date) { $this->date_inscription = $date; return $this; }
    public function setRole($role) { $allowed_roles = ['patient','admin','medecin']; if(in_array($role,$allowed_roles)) $this->role=$role; return $this; }
    public function setStatut($statut) { $allowed_status = ['actif','inactif','en_attente','suspendu','rejete']; if(in_array($statut,$allowed_status)) $this->statut=$statut; return $this; }
    public function setResetToken($token) { $this->reset_token = $token; return $this; }
    public function setResetTokenExpires($expires) { $this->reset_token_expires = $expires; return $this; }
    public function setDiplomePath($diplome_path) { $this->diplome_path = $diplome_path; return $this; }
    public function setHistoriqueConnexions($historique) { $this->historique_connexions = $historique; return $this; }
    public function setDiplomeStatut($diplome_statut) { $allowed_status = ['en attente','validé','rejeté']; if(in_array($diplome_statut,$allowed_status)) $this->diplome_statut=$diplome_statut; return $this; }
    public function setDiplomeCommentaire($diplome_commentaire) { $this->diplome_commentaire = htmlspecialchars(trim($diplome_commentaire), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setDiplomeDateVerification($diplome_date_verification) { $this->diplome_date_verification = $diplome_date_verification; return $this; }
    public function setDerniereConnexion($derniere_connexion) { $this->derniere_connexion = $derniere_connexion; return $this; }
    public function setBio($bio) { $this->bio = htmlspecialchars(trim($bio), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setIdService($idService) { $this->idService = (int)$idService; return $this; }
    public function setHeure1Debut($heure1_debut) { $this->heure1_debut = $heure1_debut; return $this; }
    public function setHeure1Fin($heure1_fin) { $this->heure1_fin = $heure1_fin; return $this; }
    public function setHeure2Debut($heure2_debut) { $this->heure2_debut = $heure2_debut; return $this; }
    public function setHeure2Fin($heure2_fin) { $this->heure2_fin = $heure2_fin; return $this; }
    public function setHeure3Debut($heure3_debut) { $this->heure3_debut = $heure3_debut; return $this; }
    public function setHeure3Fin($heure3_fin) { $this->heure3_fin = $heure3_fin; return $this; }
    public function setHeure4Debut($heure4_debut) { $this->heure4_debut = $heure4_debut; return $this; }
    public function setHeure4Fin($heure4_fin) { $this->heure4_fin = $heure4_fin; return $this; }
    public function setImage($image) { $this->image = $image; return $this; }
    public function setNoteGlobale($note_globale) { $this->note_globale = (float)$note_globale; return $this; }
    public function setNbAvis($nb_avis) { $this->nb_avis = (int)$nb_avis; return $this; }
    public function setLangues($langues) { $this->langues = htmlspecialchars(trim($langues), ENT_QUOTES, 'UTF-8'); return $this; }
    public function setPrixConsultation($prix_consultation) { $this->prix_consultation = $prix_consultation; return $this; }
    public function setExperience($experience) { $this->experience = $experience; return $this; }
    public function setSpecialite($specialite) { $this->specialite = htmlspecialchars(trim($specialite), ENT_QUOTES, 'UTF-8'); return $this; }

    public function getPhotoProfilUrl(): string {
        if (!empty($this->image)) {
            return strpos($this->image, 'http') === 0 ? $this->image : '/uploads/profiles/' . $this->image;
        }
        return '/assets/images/default-avatar.png';
    }

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
        return !empty($this->image);
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
    
    public function estSuspendu(): bool {
        return $this->statut === 'suspendu';
    }
    
    public function estRejete(): bool {
        return $this->statut === 'rejete';
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
            return "Jamais";
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
    
    public function getDiplomeUrl(): ?string {
        if (empty($this->diplome_path)) {
            return null;
        }
        
        return '/uploads/diplomes/' . $this->diplome_path;
    }
    
    public function generateResetToken(): string {
        $token = bin2hex(random_bytes(32));
        $this->reset_token = $token;
        $this->reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        return $token;
    }
    
    public function isResetTokenValid(): bool {
        if (empty($this->reset_token) || empty($this->reset_token_expires)) {
            return false;
        }
        
        $now = new DateTime();
        $expires = new DateTime($this->reset_token_expires);
        
        return $now < $expires;
    }
    
    public function clearResetToken(): void {
        $this->reset_token = null;
        $this->reset_token_expires = null;
    }
    
    public function addConnexionHistory($connexion_data) {
        $history = $this->getConnexionHistoryArray();
        $history[] = $connexion_data;
        $this->historique_connexions = json_encode($history, JSON_PRETTY_PRINT);
        return $this;
    }
    
    public function getConnexionHistoryArray(): array {
        if (empty($this->historique_connexions)) {
            return [];
        }
        
        $history = json_decode($this->historique_connexions, true);
        return is_array($history) ? $history : [];
    }
    
    public function getStatutLabel(): string {
        $labels = [
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            'en_attente' => 'En attente',
            'suspendu' => 'Suspendu',
            'rejete' => 'Rejeté'
        ];
        
        return $labels[$this->statut] ?? $this->statut;
    }
    
    public function getRoleLabel(): string {
        $labels = [
            'patient' => 'Patient',
            'medecin' => 'Médecin',
            'admin' => 'Administrateur'
        ];
        
        return $labels[$this->role] ?? $this->role;
    }
    
    public function getDiplomeStatutLabel(): string {
        $labels = [
            'en attente' => 'En attente',
            'validé' => 'Validé',
            'rejeté' => 'Rejeté'
        ];
        
        return $labels[$this->diplome_statut] ?? $this->diplome_statut;
    }
    
    public function getHeuresTravail(): array {
        $heures = [];
        
        if (!empty($this->heure1_debut) && !empty($this->heure1_fin)) {
            $heures[] = ['debut' => $this->heure1_debut, 'fin' => $this->heure1_fin];
        }
        if (!empty($this->heure2_debut) && !empty($this->heure2_fin)) {
            $heures[] = ['debut' => $this->heure2_debut, 'fin' => $this->heure2_fin];
        }
        if (!empty($this->heure3_debut) && !empty($this->heure3_fin)) {
            $heures[] = ['debut' => $this->heure3_debut, 'fin' => $this->heure3_fin];
        }
        if (!empty($this->heure4_debut) && !empty($this->heure4_fin)) {
            $heures[] = ['debut' => $this->heure4_debut, 'fin' => $this->heure4_fin];
        }
        
        return $heures;
    }
    
    public function getNoteFormatee(): string {
        return number_format($this->note_globale, 1, ',', ' ');
    }
    
    public function getLanguesArray(): array {
        if (empty($this->langues)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->langues));
    }
    
    public function getPrixFormate(): ?string {
        if (empty($this->prix_consultation)) {
            return null;
        }
        
        return $this->prix_consultation . ' MAD';
    }
    
    public function toArray(): array {
        return [
            'id_utilisateur' => $this->id_utilisateur,
            'username' => $this->username,
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
            'image' => $this->image,
            'diplome_path' => $this->diplome_path,
            'historique_connexions' => $this->historique_connexions,
            'diplome_statut' => $this->diplome_statut,
            'diplome_commentaire' => $this->diplome_commentaire,
            'diplome_date_verification' => $this->diplome_date_verification,
            'specialite' => $this->specialite,
            'derniere_connexion' => $this->derniere_connexion,
            'bio' => $this->bio,
            'idService' => $this->idService,
            'heure1_debut' => $this->heure1_debut,
            'heure1_fin' => $this->heure1_fin,
            'heure2_debut' => $this->heure2_debut,
            'heure2_fin' => $this->heure2_fin,
            'heure3_debut' => $this->heure3_debut,
            'heure3_fin' => $this->heure3_fin,
            'heure4_debut' => $this->heure4_debut,
            'heure4_fin' => $this->heure4_fin,
            'note_globale' => $this->note_globale,
            'nb_avis' => $this->nb_avis,
            'langues' => $this->langues,
            'prix_consultation' => $this->prix_consultation,
            'experience' => $this->experience
        ];
    }
    
    public function addAvis($note): void {
        $total_notes = $this->note_globale * $this->nb_avis;
        $this->nb_avis++;
        $total_notes += $note;
        $this->note_globale = $total_notes / $this->nb_avis;
    }
    
    public function isDisponible($heure, $date): bool {
     
        return true;
    }
}