<?php

class User
{
    private $id_utilisateur;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $role;
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

    // Getters
    public function getIdUtilisateur()
    {
        return $this->id_utilisateur;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getMotDePasse()
    {
        return $this->mot_de_passe;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getIdService()
    {
        return $this->idService;
    }

    public function getHeure1_debut()
    {
        return $this->heure1_debut;
    }
    public function getHeure1_fin()
    {
        return $this->heure1_fin;
    }
    public function getHeure2_debut()
    {
        return $this->heure2_debut;
    }
    public function getHeure2_fin()
    {
        return $this->heure2_fin;
    }
    public function getHeure3_debut()
    {
        return $this->heure3_debut;
    }
    public function getHeure3_fin()
    {
        return $this->heure3_fin;
    }
    public function getHeure4_debut()
    {
        return $this->heure4_debut;
    }
    public function getHeure4_fin()
    {
        return $this->heure4_fin;
    }

    // Setters
    public function setIdUtilisateur($id_utilisateur)
    {
        $this->id_utilisateur = $id_utilisateur;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setMotDePasse($mot_de_passe)
    {
        $this->mot_de_passe = $mot_de_passe;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setIdService($idService)
    {
        $this->idService = $idService;
    }

    public function setHeure1_debut($heure1_debut)
    {
        $this->heure1_debut = $heure1_debut;
    }
    public function setHeure1_fin($heure1_fin)
    {
        $this->heure1_fin = $heure1_fin;
    }
    public function setHeure2_debut($heure2_debut)
    {
        $this->heure2_debut = $heure2_debut;
    }
    public function setHeure2_fin($heure2_fin)
    {
        $this->heure2_fin = $heure2_fin;
    }
    public function setHeure3_debut($heure3_debut)
    {
        $this->heure3_debut = $heure3_debut;
    }
    public function setHeure3_fin($heure3_fin)
    {
        $this->heure3_fin = $heure3_fin;
    }
    public function setHeure4_debut($heure4_debut)
    {
        $this->heure4_debut = $heure4_debut;
    }
    public function setHeure4_fin($heure4_fin)
    {
        $this->heure4_fin = $heure4_fin;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
    private $note_globale;
    private $nb_avis;

    public function getNoteGlobale()
    {
        return $this->note_globale;
    }

    public function setNoteGlobale($note_globale)
    {
        $this->note_globale = $note_globale;
    }

    public function getNbAvis()
    {
        return $this->nb_avis;
    }

    public function setNbAvis($nb_avis)
    {
        $this->nb_avis = $nb_avis;
    }
    private $bio;

    public function getBio()
    {
        return $this->bio;
    }

    public function setBio($bio)
    {
        $this->bio = $bio;
    }
    private $langues;
    private $prix_consultation;
    private $experience;

    public function getLangues()
    {
        return $this->langues;
    }

    public function setLangues($langues)
    {
        $this->langues = $langues;
    }

    public function getPrixConsultation()
    {
        return $this->prix_consultation;
    }

    public function setPrixConsultation($prix_consultation)
    {
        $this->prix_consultation = $prix_consultation;
    }

    public function getExperience()
    {
        return $this->experience;
    }

    public function setExperience($experience)
    {
        $this->experience = $experience;
    }
    private $adresse;

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }
}
