<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
   
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
   
    $jsonInput = file_get_contents('php://input');
    
    if (!$jsonInput) {
        throw new Exception('Aucune donnée reçue');
    }

    $input = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

   
    file_put_contents('ai_debug.log', date('Y-m-d H:i:s') . " - Données reçues: " . json_encode($input) . "\n", FILE_APPEND);

    
    $correct = [];
    $suggestions = [];
    $confidence_score = 100;

    function smartCorrect($value, $type) {
        if (empty($value)) return $value;
        
        $value = trim($value);
        
        switch($type) {
            case 'nom':
            case 'prenom':
              
                $value = preg_replace('/\s+/', ' ', $value);
                $value = mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
                $value = preg_replace('/\b([DdLl])\s+\'/', "$1'", $value);
                break;
                
            case 'email':
                $value = strtolower($value);
                
                $common_typos = [
                    'gmial.com' => 'gmail.com',
                    'gmal.com' => 'gmail.com',
                    'gmaill.com' => 'gmail.com',
                    'yaho.com' => 'yahoo.com',
                    'yahooo.com' => 'yahoo.com',
                    'hotmal.com' => 'hotmail.com',
                    'hotmial.com' => 'hotmail.com',
                    'oulook.com' => 'outlook.com',
                    'outloook.com' => 'outlook.com',
                    'gmil.com' => 'gmail.com',
                ];
                
                foreach($common_typos as $wrong => $right) {
                    if (str_contains($value, $wrong)) {
                        $value = str_replace($wrong, $right, $value);
                    }
                }
                break;
                
            case 'adresse':
             
                $replacements = [
                    '/\bav\.?\b/i' => 'Avenue',
                    '/\bbd\.?\b/i' => 'Boulevard',
                    '/\brue\b/i' => 'rue',
                    '/\bstr\.?\b/i' => 'Street',
                    '/\bapt\.?\b/i' => 'Appartement',
                    '/\bch\.?\b/i' => 'Chemin',
                    '/\bpl\.?\b/i' => 'Place',
                    '/\bimmeuble\b/i' => 'Immeuble',
                    '/\bresidence\b/i' => 'Résidence',
                ];
                
                foreach($replacements as $pattern => $replacement) {
                    $value = preg_replace($pattern, $replacement, $value);
                }
                
               
                $value = preg_replace_callback('/(\b(?:Avenue|Boulevard|Rue|Place|Allée|Impasse|Route|Chemin|Square)\b)/i', 
                    function($matches) {
                        return ucfirst(strtolower($matches[1]));
                    }, $value);
                break;
                
            case 'langues':
                $langues = array_map('trim', explode(',', $value));
                $corrected_langues = [];
                $common_langues = [
                    'Français', 'Anglais', 'Espagnol', 'Allemand', 'Arabe',
                    'Italien', 'Portugais', 'Chinois', 'Russe', 'Japonais',
                    'Néerlandais', 'Grec', 'Turc', 'Polonais'
                ];
                
                foreach($langues as $langue) {
                    $langue = trim($langue);
                    if (!empty($langue)) {
                        
                        $best_match = $langue;
                        $min_distance = PHP_INT_MAX;
                        
                        foreach($common_langues as $common) {
                            $distance = levenshtein(strtolower($langue), strtolower($common));
                            if ($distance < $min_distance && $distance <= 2) {
                                $min_distance = $distance;
                                $best_match = $common;
                            }
                        }
                        
                        $corrected_langues[] = ucfirst($best_match);
                    }
                }
                
                $value = implode(', ', array_unique($corrected_langues));
                break;
                
            case 'experience':
                $value = intval($value);
                if ($value < 0) $value = 0;
                if ($value > 60) $value = 60;
                break;
                
            
            case 'prix_consultation':
                $value = floatval($value);
                if ($value < 0) $value = 0;
                if ($value > 500) $value = 500;
                $value = number_format($value, 2, '.', '');
                break;
                
        
            case 'bio':
           
                if (strlen($value) > 500) {
                    $value = substr($value, 0, 500);
                }
               
                if (!empty($value)) {
                    $value = ucfirst($value);
                }
                break;
        }
        
        return $value;
    }

  
    $correct['nom'] = isset($input['nom']) ? smartCorrect($input['nom'], 'nom') : '';
    $correct['prenom'] = isset($input['prenom']) ? smartCorrect($input['prenom'], 'prenom') : '';
    $correct['email'] = isset($input['email']) ? smartCorrect($input['email'], 'email') : '';
    $correct['adresse'] = isset($input['adresse']) ? smartCorrect($input['adresse'], 'adresse') : '';
    $correct['langues'] = isset($input['langues']) ? smartCorrect($input['langues'], 'langues') : '';
    $correct['experience'] = isset($input['experience']) ? smartCorrect($input['experience'], 'experience') : '';
    $correct['prix_consultation'] = isset($input['prix_consultation']) ? smartCorrect($input['prix_consultation'], 'prix_consultation') : '';
    $correct['bio'] = isset($input['bio']) ? smartCorrect($input['bio'], 'bio') : '';

   
    if (!empty($correct['email']) && !filter_var($correct['email'], FILTER_VALIDATE_EMAIL)) {
        $suggestions[] = "L'adresse email semble incorrecte. Veuillez vérifier.";
        $confidence_score -= 20;
    }

    
    if (strlen($correct['nom']) < 2) {
        $suggestions[] = "Le nom semble trop court";
        $confidence_score -= 10;
    }

    if (strlen($correct['prenom']) < 2) {
        $suggestions[] = "Le prénom semble trop court";
        $confidence_score -= 10;
    }

    
    if (!empty($correct['adresse']) && strlen($correct['adresse']) < 10) {
        $suggestions[] = "L'adresse semble incomplète. Assurez-vous d'inclure le numéro, la rue, le code postal et la ville";
        $confidence_score -= 15;
    }

   
    if (!empty($correct['experience'])) {
        $exp = intval($correct['experience']);
        if ($exp > 40) {
            $suggestions[] = "Expérience importante détectée. C'est un atout !";
        } elseif ($exp == 0) {
            $suggestions[] = "Débutant détecté. Pensez à mentionner vos stages et formations.";
        }
    }

   
    if (!empty($correct['prix_consultation'])) {
        $prix = floatval($correct['prix_consultation']);
        if ($prix < 20) {
            $suggestions[] = "Prix de consultation très bas. Pensez à votre rémunération.";
            $confidence_score -= 10;
        } elseif ($prix > 200) {
            $suggestions[] = "Prix de consultation élevé. Assurez-vous qu'il correspond au marché.";
            $confidence_score -= 10;
        } elseif ($prix >= 50 && $prix <= 100) {
            $suggestions[] = "Prix de consultation dans la moyenne nationale.";
        }
    }

    
    if (!empty($correct['bio'])) {
        $bio_length = strlen($correct['bio']);
        if ($bio_length < 50) {
            $suggestions[] = "Votre biographie est très courte. Pensez à décrire votre parcours et spécialités.";
            $confidence_score -= 15;
        } elseif ($bio_length > 450) {
            $suggestions[] = "Votre biographie est très détaillée. Les patients apprécieront !";
        }
        
       
        $word_count = str_word_count($correct['bio']);
        if ($word_count < 10) {
            $suggestions[] = "Votre biographie pourrait être plus développée.";
        } elseif ($word_count > 100) {
            $suggestions[] = "Biographie très complète. Excellent pour attirer les patients !";
        }
        
        
        $keywords = ['médecin', 'spécialité', 'formation', 'expérience', 'patient', 'soin'];
        $found_keywords = 0;
        foreach($keywords as $keyword) {
            if (stripos($correct['bio'], $keyword) !== false) {
                $found_keywords++;
            }
        }
        
        if ($found_keywords < 2 && $bio_length > 100) {
            $suggestions[] = "Pensez à mentionner vos spécialités, formation et approche avec les patients.";
        }
    }

    if ($confidence_score < 0) $confidence_score = 0;
    if ($confidence_score > 100) $confidence_score = 100;

    if (empty($suggestions)) {
        $suggestions[] = "Toutes vos informations semblent correctes et bien renseignées !";
    }

    $response = [
        'success' => true,
        'correct' => $correct,
        'suggestions' => $suggestions,
        'confidence_score' => $confidence_score,
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => 'Vérification IA terminée avec succès'
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>