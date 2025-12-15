<?php
session_start();
header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 1);


function getLocalResponse($message) {
    $responses = [
        'bonjour' => 'Bonjour ! 👋 Je suis Medsense AI, votre assistant médical virtuel. Comment puis-je vous aider aujourd\'hui ?',
        'bonsoir' => 'Bonsoir ! 🌙 Je suis Medsense AI. Comment puis-je vous assister ce soir ?',
        'salut' => 'Salut ! 😊 Je suis Medsense, votre assistant médical. En quoi puis-je vous aider ?',
        
        
        'rendez-vous' => 'Pour prendre un rendez-vous :<br>1. Connectez-vous à votre compte<br>2. Allez dans "Prendre RDV"<br>3. Choisissez un médecin et une date<br>4. Confirmez votre rendez-vous<br><br>Vous pouvez aussi appeler le 01 23 45 67 89.',
        'rdv' => '📅 Pour un rendez-vous, connectez-vous et allez dans la section "Rendez-vous". Nos médecins sont disponibles du lundi au vendredi de 8h à 20h.',
        'prendre rendez-vous' => 'Pour prendre rendez-vous en ligne :<br>• Connectez-vous à votre compte patient<br>• Sélectionnez "Nouveau rendez-vous"<br>• Choisissez votre spécialité et médecin<br>• Sélectionnez une date et heure disponible',
        'docteur' => '👨‍⚕️ Nos médecins sont des professionnels qualifiés dans plus de 50 spécialités. Vous pouvez consulter leurs profils dans la section "Doctors".',
        'médecin' => 'Nous avons 150 médecins experts dans toutes les spécialités. Pour consulter un médecin, prenez rendez-vous en ligne ou appelez-nous.',
      
        'urgence' => '🚨 EN CAS D\'URGENCE :<br>• Composez le 15 (SAMU)<br>• Ou le 112 (urgence européenne)<br>• Ou rendez-vous aux urgences les plus proches<br><br>Notre service d\'urgence est disponible 24h/24 au 01 23 45 67 89.',
        'urgent' => '🚑 Pour une urgence médicale :<br>1. Composez immédiatement le 15 ou 112<br>2. Ne vous déplacez pas seul si possible<br>3. Préparez vos documents médicaux<br><br>Notre équipe peut vous orienter au 01 23 45 67 89.',
        
        
        'réclamation' => '📝 Pour déposer une réclamation :<br>1. Connectez-vous à votre compte<br>2. Allez dans "Mes réclamations"<br>3. Remplissez le formulaire en détaillant votre demande<br>4. Notre équite traitera votre demande sous 48h.',
        'plainte' => 'Pour déposer une plainte ou réclamation, utilisez le formulaire dans votre espace patient ou envoyez un email à reclamation@medcare.com.',
        
      
        'contact' => '📞 Contactez-nous :<br>• Téléphone : 01 23 45 67 89<br>• Email : contact@medsense.com<br>• Adresse : 123 Rue de la Santé, Paris<br>• Horaires : Lun-Ven 8h-20h, Sam 9h-13h',
        'adresse' => '📍 Notre adresse :<br>MedCare Medical<br>123 Rue de la Santé<br>75000 Paris<br><br>Métro : Station Santé (ligne 4)',
        
        
        'blog' => '📚 Notre blog médical :<br>• Articles sur la santé et prévention<br>• Conseils de nos médecins<br>• Actualités médicales<br>• Témoignages de patients<br><br>Accédez-y via le menu "Blog".',
        'article' => 'Nos médecins publient régulièrement des articles sur la santé, la prévention et les nouvelles technologies médicales. Consultez notre blog pour en savoir plus.',
        
        'horaire' => '🕒 Nos horaires :<br>• Lundi - Vendredi : 8h - 20h<br>• Samedi : 9h - 13h<br>• Dimanche : Urgences uniquement<br>• Téléphone : 24h/24 pour les urgences',
        'ouvert' => 'Nous sommes ouverts du lundi au vendredi de 8h à 20h, et le samedi de 9h à 13h. Pour les urgences, nous sommes joignables 24h/24 au 01 23 45 67 89.',
        
        'service' => '🏥 Nos services :<br>• Consultations générales et spécialisées<br>• Examens médicaux<br>• Suivi de santé<br>• Téléconsultation<br>• Urgences<br>• Bilans de santé',
        'spécialité' => 'Nous proposons plus de 50 spécialités médicales : cardiologie, dermatologie, pédiatrie, gynécologie, orthopédie, et bien d\'autres.',
        
        'prix' => '💰 Nos tarifs :<br>• Consultation générale : 25€<br>• Consultation spécialiste : 30-50€<br>• Certaines consultations sont remboursées par la sécurité sociale et mutuelles.<br>Pour un devis précis, contactez notre secrétariat.',
        'tarif' => 'Les tarifs varient selon le type de consultation et le médecin. La plupart des consultations sont conventionnées et remboursées.',
        
        'fonctionnalité' => '🌟 Fonctionnalités du site :<br>• Prise de rendez-vous en ligne<br>• Espace patient personnel<br>• Blog médical<br>• Dépôt de réclamations<br>• Urgences 24h/24<br>• Téléconsultation',
        
        'default' => 'Je comprends que vous demandez : "' . $message . '". Pour plus d\'informations, je vous recommande de :<br>• Consulter notre site web<br>• Appeler notre secrétariat au 01 23 45 67 89<br>• Envoyer un email à contact@medcare.com<br><br>Comment puis-je vous aider davantage ?'
    ];
    
    $lowerMessage = strtolower($message);
    
  
    foreach ($responses as $key => $response) {
        if (strpos($lowerMessage, $key) !== false) {
            return $response;
        }
    }
    
    return $responses['default'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? $_POST['message'] ?? '';
    
    if (empty($message)) {
        echo json_encode(['error' => 'Message vide']);
        exit;
    }
    $responseText = getLocalResponse($message);
    echo json_encode([
        "choices" => [[
            "message" => [
                "content" => $responseText
            ]
        ]]
    ]);
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>