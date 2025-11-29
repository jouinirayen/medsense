<?php
/**
 * RendezVous Model
 * Représente un rendez-vous avec ses attributs
 */

class RendezVousModel {
    
    // Attributs de la classe
    private $id;
    private $serviceId;
    private $appointmentDate;
    private $appointmentTime;
    private $isBooked;
    private $bookedEmail;
    private $createdAt;
    private $updatedAt;
    
    public function __construct($id = null, $serviceId = null, $appointmentDate = null, $appointmentTime = null, $isBooked = 0, $bookedEmail = null, $createdAt = null, $updatedAt = null) {
        $this->id = $id;
        $this->serviceId = $serviceId;
        $this->appointmentDate = $appointmentDate;
        $this->appointmentTime = $appointmentTime;
        $this->isBooked = $isBooked;
        $this->bookedEmail = $bookedEmail;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getServiceId() {
        return $this->serviceId;
    }
    
    public function getAppointmentDate() {
        return $this->appointmentDate;
    }
    
    public function getAppointmentTime() {
        return $this->appointmentTime;
    }
    
    public function getIsBooked() {
        return $this->isBooked;
    }
    
    public function getBookedEmail() {
        return $this->bookedEmail;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
    
    // Setters
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setServiceId($serviceId) {
        $this->serviceId = $serviceId;
    }
    
    public function setAppointmentDate($appointmentDate) {
        $this->appointmentDate = $appointmentDate;
    }
    
    public function setAppointmentTime($appointmentTime) {
        $this->appointmentTime = $appointmentTime;
    }
    
    public function setIsBooked($isBooked) {
        $this->isBooked = $isBooked;
    }
    
    public function setBookedEmail($bookedEmail) {
        $this->bookedEmail = $bookedEmail;
    }
    
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }
    
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'service_id' => $this->serviceId,
            'appointment_date' => $this->appointmentDate,
            'appointment_time' => $this->appointmentTime,
            'is_booked' => $this->isBooked,
            'booked_email' => $this->bookedEmail,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
?>
