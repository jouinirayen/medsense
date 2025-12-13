<?php
class GeminiAssistantController {
    private $api_key;
    private $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    public function __construct() {
        $this->api_key = 'AIzaSyAyGejWqEDw90zTnOyIpoL4dqfdLBwYU2w';
    }
    
    public function askQuestion($question, $context = '') {
        try {
            if (empty($this->api_key) || $this->api_key === 'AIzaSyAyGejWqEDw90zTnOyIpoL4dqfdLBwYU2w') {
                throw new Exception('Assistant non configuré. Veuillez configurer la clé API.');
            }
            $question = trim($question);
            if (empty($question)) {
                throw new Exception('Veuillez poser une question');
            }
            
            if (strlen($question) > 500) {
                throw new Exception('Question trop longue (max 500 caractères)');
            }
            $prompt = $this->buildPrompt($question, $context);
            $response_text = $this->callAPI($prompt);
            
            return [
                'success' => true,
                'response' => $this->formatResponse($response_text),
                'timestamp' => date('H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null
            ];
        }
    }
    
    private function buildPrompt($question, $context) {
        $base_prompt = "Tu es MedSense AI, l'assistant intelligent de Medsense Medical. 
        Tu aides les utilisateurs (patients, médecins, administrateurs) à utiliser la plateforme.
        
        Règles strictes :
        1. Questions médicales : « Consultez un professionnel de santé »
        2. Réponses en français, claires et précises
        3. Formatage simple (listes, gras pour les points importants)
        4. Maximum 300 mots par réponse
        5. Référence aux fonctionnalités de Medsense quand pertinent
        
        Contexte utilisateur : $context
        
        Question : $question
        
        Réponse :";
        
        return $base_prompt;
    }
    
    private function callAPI($prompt) {
        // Préparer les données pour Gemini
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ]
        ];
        
        // Configuration cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_url . '?key=' . $this->api_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        // Exécuter la requête
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Erreur de connexion: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Traiter la réponse
        if ($http_code !== 200) {
            throw new Exception('Erreur API (Code ' . $http_code . ')');
        }
        
        $response_data = json_decode($response, true);
        
        if (empty($response_data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Réponse invalide de l\'API');
        }
        
        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    }
    
    private function formatResponse($text) {
        $text = trim($text);
        $text = preg_replace('/\n- /', "\n• ", $text);
        $text = preg_replace('/\n\d+\. /', "\n", $text);
        $text = nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
        $text = preg_replace('/^(.*?:)/m', '<strong>$1</strong>', $text);
        
        return $text;
    }
    
    public function getQuickSuggestions($user_type) {
        $suggestions = [
            'patient' => [
                'Comment prendre rendez-vous ?',
                'Comment accéder à mes ordonnances ?',
                'Où trouver mes résultats d\'analyses ?',
                'Comment contacter mon médecin ?',
                'Comment mettre à jour mes informations ?'
            ],
            'medecin' => [
                'Comment gérer mon agenda ?',
                'Comment consulter un dossier patient ?',
                'Comment créer une ordonnance ?',
                'Comment voir mes consultations du jour ?',
                'Comment générer un certificat médical ?'
            ],
            'admin' => [
                'Comment gérer les utilisateurs ?',
                'Comment voir les statistiques ?',
                'Comment modifier les paramètres ?',
                'Comment générer un rapport ?',
                'Comment gérer les rôles ?'
            ]
        ];
        
        return $suggestions[$user_type] ?? [
            'Comment utiliser la plateforme ?',
            'Où trouver de l\'aide ?',
            'Comment contacter le support ?'
        ];
    }
}
?>