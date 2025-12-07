<?php

class Reservation
{
    private $idRDV;
    private $idMedecin;
    private $heureRdv;
    private $patientNom;
    private $patientPrenom;
    private $statut;
    private $idPatient;
    private $date; // Added date property

    // Properties for joined data
    private $medecinNom;
    private $medecinPrenom;
    private $serviceNom;
    private $patientEmail;

    public function __construct($idRDV = null, $idMedecin = null, $heureRdv = null, $patientNom = null, $patientPrenom = null, $statut = null, $idPatient = null, $date = null)
    {
        $this->idRDV = $idRDV;
        $this->idMedecin = $idMedecin;
        $this->heureRdv = $heureRdv;
        $this->patientNom = $patientNom;
        $this->patientPrenom = $patientPrenom;
        $this->statut = $statut;
        $this->idPatient = $idPatient;
        $this->date = $date;
    }

    // Getters
    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getIdRDV()
    {
        return $this->idRDV;
    }

    public function getIdMedecin()
    {
        return $this->idMedecin;
    }

    public function getHeureRdv()
    {
        return $this->heureRdv;
    }

    public function getPatientNom()
    {
        return $this->patientNom;
    }

    public function getPatientPrenom()
    {
        return $this->patientPrenom;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function getIdPatient()
    {
        return $this->idPatient;
    }

    public function getMedecinNom()
    {
        return $this->medecinNom;
    }
    public function getMedecinPrenom()
    {
        return $this->medecinPrenom;
    }
    public function getServiceNom()
    {
        return $this->serviceNom;
    }
    public function getPatientEmail()
    {
        return $this->patientEmail;
    }

    // Setters
    public function setIdRDV($idRDV)
    {
        $this->idRDV = $idRDV;
    }

    public function setIdMedecin($idMedecin)
    {
        $this->idMedecin = $idMedecin;
    }

    public function setHeureRdv($heureRdv)
    {
        $this->heureRdv = $heureRdv;
    }

    public function setPatientNom($patientNom)
    {
        $this->patientNom = $patientNom;
    }

    public function setPatientPrenom($patientPrenom)
    {
        $this->patientPrenom = $patientPrenom;
    }

    public function setStatut($statut)
    {
        $this->statut = $statut;
    }

    public function setIdPatient($idPatient)
    {
        $this->idPatient = $idPatient;
    }

    public function setMedecinNom($medecinNom)
    {
        $this->medecinNom = $medecinNom;
    }
    public function setMedecinPrenom($medecinPrenom)
    {
        $this->medecinPrenom = $medecinPrenom;
    }
    public function setServiceNom($serviceNom)
    {
        $this->serviceNom = $serviceNom;
    }
    public function setPatientEmail($patientEmail)
    {
        $this->patientEmail = $patientEmail;
    }
    private $note;

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    private $idService;

    public function getIdService()
    {
        return $this->idService;
    }

    public function setIdService($idService)
    {
        $this->idService = $idService;
    }
}
