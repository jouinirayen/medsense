<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ServiceModel.php';

class ChatController
{
    private $apiKey = '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';
    private $siteUrl = 'http://localhost/projet2025/'; // Adjust based on actual environment

    public function handleChat($userMessage)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // FORCE RESET HISTORY for debugging to pick up new prompt
        // Remove this in production or make it conditional
        // $_SESSION['chat_history'] = []; 

        // Actually, let's just clear it if the system prompt has changed significantly
        // For now, simpler: user says "le meme probleme", implies we should clear history logic
        // Let's add a keyword check or just rely on the user clearing cookies, but since I can't do that:
        // I will clear it once here.
        if (isset($_GET['reset_chat'])) {
            $_SESSION['chat_history'] = [];
        }

        // 1. Gather Context (Services & Doctors)
        // Caching context in session to improve speed (avoid DB query every time)
        // Changed to v4 to force refresh with relaxed rules
        // Knowledge Base (FAQ & definitions)
        $contextData = "INFORMATION PLATEFORME 'Medsense':\n" .
            "- C'est quoi ? : Une plateforme de prise de rendez-vous médicaux en ligne qui connecte patients et médecins.\n" .
            "- Fonctionnement : L'utilisateur se connecte, choisit un service, sélectionne un médecin et réserve un créneau horaire.\n" .
            "- Comment prendre rendez-vous ? : 1. Rechercher un service. 2. Choisir un médecin. 3. Cliquer sur un créneau horaire libre (blanc). 4. Confirmer.\n" .
            "- Comment annuler ? : Aller dans la page 'Mes Rendez-vous', trouver le RDV et cliquer sur le bouton 'Annuler'.\n" .
            "- Créer un compte ? : Cliquer sur 'S'inscrire' sur la page d'accueil et remplir le formulaire.\n" .
            "- Sécurité : Vos données sont confidentielles et sécurisées.\n\n";

        try {
            $pdo = (new config())->getConnexion();

            // Fetch Services
            $stmt = $pdo->query("SELECT * FROM services");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($services)) {
                $contextData .= "\nÉTAT ACTUEL DU SITE : AUCUN SERVICE DISPONIBLE.\n";
                $contextData .= "SI L'UTILISATEUR DEMANDE LES SERVICES, DITES CLAIREMENT : 'Il n'y a aucun service disponible pour le moment'.\n";
            } else {
                // Fetch Doctors with full details
                $stmtDoc = $pdo->prepare("SELECT id_utilisateur, nom, prenom, adresse, heure1_debut, heure1_fin, heure2_debut, heure2_fin, heure3_debut, heure3_fin, heure4_debut, heure4_fin, prix_consultation, experience, langues, note_globale FROM utilisateur WHERE role = 'medecin' AND idService = ?");

                // Fetch upcoming bookings for context (Next 7 days)
                $stmtBooked = $pdo->prepare("SELECT date, heureRdv FROM rendezvous WHERE idMedecin = ? AND statut IN ('pris', 'confirme', 'en attente') AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");

                foreach ($services as $s) {
                    $contextData .= "SERVICE: " . $s['name'] . " (ID:" . $s['id'] . ")\n";

                    $stmtDoc->execute([$s['id']]);
                    $doctors = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($doctors)) {
                        foreach ($doctors as $d) {
                            $contextData .= " - Dr. " . $d['nom'] . " " . $d['prenom'] . " (Ville: " . ($d['adresse'] ?: 'Non spécifiée') . ")\n";
                            $contextData .= "   * Langues: " . ($d['langues'] ?: 'Non spécifié') . "\n";
                            $contextData .= "   * Expérience: " . ($d['experience'] ?: 'Non spécifiée') . "\n";
                            $contextData .= "   * Prix: " . ($d['prix_consultation'] ? $d['prix_consultation'] . ' TND' : 'Non spécifié') . "\n";
                            $contextData .= "   * Note: " . ($d['note_globale'] ? number_format($d['note_globale'], 1) . "/5" : 'Aucune note') . "\n";

                            // Fetch Booked Slots
                            $stmtBooked->execute([$d['id_utilisateur']]);
                            $booked = $stmtBooked->fetchAll(PDO::FETCH_ASSOC);
                            $bookedList = [];
                            foreach ($booked as $b) {
                                // key: YYYY-MM-DD HH:mm
                                $bookedList[$b['date'] . ' ' . substr($b['heureRdv'], 0, 5)] = true;
                            }

                            // Format Schedule
                            $contextData .= "   * CRÉNEAUX GÉNÉRAUX (Vérifier disponibilité réelle ci-dessous):\n";
                            for ($i = 1; $i <= 4; $i++) {
                                if (!empty($d["heure{$i}_debut"])) {
                                    $debut = date('H:i', strtotime($d["heure{$i}_debut"]));
                                    $fin = date('H:i', strtotime($d["heure{$i}_fin"]));
                                    $contextData .= "     Slot $i: $debut\n";
                                }
                            }

                            // List specifically OCCUPIED slots to exclude
                            if (!empty($bookedList)) {
                                $contextData .= "   * CRÉNEAUX DÉJÀ RÉSERVÉS (INDISPONIBLES) :\n";
                                foreach (array_keys($bookedList) as $datetime) {
                                    $contextData .= "     $datetime\n";
                                }
                            } else {
                                $contextData .= "   * Aucun créneau réservé pour les 7 prochains jours.\n";
                            }
                            $contextData .= "\n";
                        }
                    } else {
                        $contextData .= " - Aucun médecin disponible pour ce service.\n\n";
                    }
                }
            }
            // Cache removed to ensure real-time updates
        } catch (Exception $e) {
            $contextData = "Erreur DB.";
        }

        // 2. Prepare System Prompt with Persona
        $systemPrompt = "Tu es l'assistant intelligent de 'Medsense'.\n" .
            "BASE DE CONNAISSANCES (Infos site + Données médicales) :\n" . $contextData . "\n" .
            "RÈGLES STRICTES DE DISPONIBILITÉ :\n" .
            "- Lorsque tu donnes les horaires d'un médecin, tu DOIS vérifier la liste 'CRÉNEAUX DÉJÀ RÉSERVÉS' associée à ce médecin.\n" .
            "- SI un créneau (Date + Heure) est dans la liste 'CRÉNEAUX DÉJÀ RÉSERVÉS', TU NE DOIS PAS le proposer comme disponible.\n" .
            "- Si un utilisateur demande 'Est-il libre le 12 mai à 14h ?' et que c'est réservé, dis NON.\n" .
            "- Propose toujours les créneaux libres les plus proches.\n" .
            "- IMPORTANT : TU NE PEUX PAS PRENDRE DE RENDEZ-VOUS. Tu ne peux que consulter les disponibilités.\n" .
            "- Si un utilisateur demande de réserver 'Je veux prendre ce rendez-vous', REPONDS UNIQUEMENT avec l'action de navigation vers le profil du médecin afin qu'il puisse réserver lui-même.\n\n" .
            "GUIDES DE COMMUNICATION (IMPORTANT POUR LA VOIX) :\n" .
            "- TU NE DOIS JAMAIS utiliser de listes à puces ( tirets '-', étoiles '*' ). C'est interdit car la synthèse vocale les lit mal.\n" .
            "- Rédige des PARAGRAPHES fluides et narratifs.\n" .
            "- Au lieu de dire : '- Lundi : 14h', dis : 'Il est disponible lundi à 14 heures'.\n" .
            "- Au lieu de dire : 'Prix : 50 TND', dis : 'Le prix de la consultation est de 50 Dinars'.\n" .
            "- Évite les abréviations : dis 'heures' pas 'h', dis 'Dinars' pas 'TND'.\n" .
            "- Ton but est d'avoir une conversation naturelle, comme un humain au téléphone.\n" .
            "- INTERACTION INTELLIGENTE : Si la demande est floue, pose une question de clarification.\n" .
            "- NAVIGATION : Si l'utilisateur veut voir un service ou réserver, réponds UNIQUEMENT le bloc JSON (sans texte avant ni après) : {\"action\": \"navigate\", \"url\": \"...\", \"text\": \"Je vous dirige vers la page...\"}.\n" .
            "- Pour la liste des médecins d'un service, utilise TOUJOURS ce format d'URL : '" . $this->siteUrl . "views/frontoffice/rendezvous_avec_docteur/doctors_list.php?service_id={ID_DU_SERVICE}'.\n" .
            "- Pour le profil d'un médecin spécifique, utilise TOUJOURS ce format d'URL : '" . $this->siteUrl . "views/frontoffice/rendezvous_avec_docteur/doctor_profile.php?doctor_id={ID_DU_MEDECIN}'.\n" .
            "- Pour tout le reste, réponds en texte normal, sans formatage complexe.\n" .
            "- Ne refuse JAMAIS de répondre si la réponse est dans la base de connaissances.";

        // 3. Manage History
        if (!isset($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }

        // AUTO-FIX: Check if history contains old/broken URLs and clear if found
        // This fixes the issue where the LLM copies the wrong pattern from previous messages
        $historyJson = json_encode($_SESSION['chat_history']);
        if (strpos($historyJson, 'views/frontoffice/doctors_list.php') !== false) {
            $_SESSION['chat_history'] = [];
        }

        // Add User Message
        $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $userMessage];

        // Keep history limited (last 6 messages + system prompt) to stay fast
        if (count($_SESSION['chat_history']) > 6) {
            $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -6);
        }

        // 4. Call API
        $response = $this->callOpenRouter($systemPrompt, $_SESSION['chat_history']);

        // Add Assistant Response
        if (isset($response['response'])) {
            // CRITICAL FIX: Intercept and correct URLs if the AI still hallucinates the old path
            $response['response'] = str_replace(
                'views/frontoffice/doctors_list.php',
                'views/frontoffice/rendezvous_avec_docteur/doctors_list.php',
                $response['response']
            );
            $response['response'] = str_replace(
                'views/frontoffice/doctor_profile.php',
                'views/frontoffice/rendezvous_avec_docteur/doctor_profile.php',
                $response['response']
            );

            $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $response['response']];
        }

        return $response;
    }





    public function generateServiceDescription($serviceName)
    {
        $systemPrompt = "Tu es un expert en communication médicale.\n" .
            "TÂCHE : Rédige une description très courte (UNE SEULE PHRASE), professionnelle, empathique et rassurante pour le service médical suivant : \"" . $serviceName . "\".\n" .
            "CIBLE : Des patients potentiels.\n" .
            "CONSIGNE : Ne mets pas de guillemets. Sois direct. Utilise un ton chaleureux. Maximum 20 mots.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']); // Clean output
        }

        return "Impossible de générer une description.";
    }

    public function analyzeSearchQuery($userQuery)
    {
        // 1. Fetch Services List
        $servicesList = "";
        try {
            $pdo = (new config())->getConnexion();
            $stmt = $pdo->query("SELECT name FROM services");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($services as $s) {
                $servicesList .= "- " . $s['name'] . "\n";
            }
        } catch (Exception $e) {
            return "0";
        }

        // 2. Build Prompt
        $systemPrompt = "Tu es un assistant de tri médical.\n" .
            "LISTE DES SERVICES DISPONIBLES :\n" . $servicesList . "\n" .
            "REQUÊTE UTILISATEUR : \"" . $userQuery . "\"\n" .
            "CONSIGNE : Analyse la requête. Retourne UNIQUEMENT le nom exact du service de la liste qui correspond le mieux au problème de l'utilisateur.\n" .
            "Exemple : Si la liste contient 'Cardiologie' et l'utilisateur dit 'mal au coeur', réponds 'Cardiologie'.\n" .
            "Si la requête ne correspond à AUCUN service médical ou n'a aucun sens, retourne '0'.\n" .
            "Ne donne aucune explication, juste le nom ou 0.";

        // 3. Call AI
        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']); // Clean output
        }

        return "0";
    }

    public function generateIconClass($serviceName)
    {
        $systemPrompt = "You are a FontAwesome 5 expert.\n" .
            "TASK: Find the best FontAwesome 5 class name for this medical service: \"" . $serviceName . "\".\n" .
            "RULES: Return ONLY the class string (e.g. 'fas fa-heartbeat', 'fas fa-tooth', 'fas fa-eye').\n" .
            "Start with 'fas fa-'. Do not add explanation. Only one class.\n" .
            "Fallback: If unsure, use 'fas fa-stethoscope'.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            $icon = trim($response['response']);
            // Cleanup just in case AI is chatty
            if (preg_match('/(fas|far|fab) fa-[a-z0-9-]+/', $icon, $matches)) {
                return $matches[0];
            }
            return $icon;
        }

        return "fas fa-stethoscope";
    }

    public function generateVisualPrompt($serviceName)
    {
        $systemPrompt = "You are an expert Medical Photographer.\n" .
            "TASK: Describe a professional, photorealistic image for the medical service: \"" . $serviceName . "\".\n" .
            "RULES: Return ONLY the English visual description keywords (comma separated).\n" .
            "Focus on: Clinical environment, Lighting, Equipment, Atmosphere.\n" .
            "Example input: 'Cardiologie' -> Output: 'Cardiology clinic, stethoscope on desk, heart monitor in background, professional blue lighting, clean modern hospital, 4k, sharp focus'.\n" .
            "NO introductory text. Just the description.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']);
        }

        // Fallback if AI fails
        return "Medical photography of " . $serviceName . ", modern hospital, professional lighting, 4k";
    }

    public function generateHealthTip($serviceName)
    {
        $systemPrompt = "You are a preventative health expert.\n" .
            "TASK: Give a short, friendly, single-sentence preventative health tip for a patient consulting in the field of: \"" . $serviceName . "\".\n" .
            "RULES: Keep it under 20 words. No introduction. Just the tip. Language: French.\n" .
            "Example: 'Ophtalmologie' -> 'Pensez à faire une pause de 20 secondes toutes les 20 minutes devant les écrans pour reposer vos yeux.'";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']);
        }

        return "N'oubliez pas de boire de l'eau régulièrement pour rester hydraté.";
    }

    public function analyzeMarketTrends($existingServices)
    {
        $date = date('d F Y');
        $servicesList = implode(", ", $existingServices);

        $systemPrompt = "You are a Medical Business Strategist.\n" .
            "CONTEXT: Current Date: $date. Existing Services: [$servicesList].\n" .
            "TASK: Identify 2 trending medical services MISSING from the list that would effectively capture current seasonal or market demand.\n" .
            "FORMAT: Return ONLY a valid JSON array: [{\"service\": \"Name\", \"reason\": \"Short reason why\", \"potential\": \"High/Medium\"}].\n" .
            "Language: French.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            // Cleanup json code blocks
            $clean = preg_replace('/^```json|```$/m', '', trim($response['response']));
            return $clean;
        }

        return '[{"service": "Dermatologie", "reason": "Demande forte", "potential": "High"}]';
    }

    public function generateDailyHealthTip()
    {
        $systemPrompt = "You are a versatile medical expert.\n" .
            "TASK: Give a unique, interesting daily health tip from a RANDOMLY SELECTED medical field (e.g. Cardiology, Dermatology, Neurology, Nutrition, Psychiatry, Ophthalmology, Pediatrics, Orthopedics, Gastroenterology, Pneumology).\n" .
            "RULES: Max 25 words. Language: French. Start directly with the tip. No intro. Ensure variety each time.\n" .
            "Example: 'Mangez une poignée d'amandes par jour pour protéger votre cœur et réduire le mauvais cholestérol.'";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']);
        }

        return "Prenez 5 minutes aujourd'hui pour respirer profondément et calmer votre esprit.";
    }

    public function generateDailyArticle()
    {
        $systemPrompt = "You are a Medical Chief Editor.\n" .
            "TASK: Create a daily featured health article.\n" .
            "1. Choose a random engaging health topic from a wide variety of fields (e.g. Neuroscience, Immunology, Genetics, Public Health, Nutrition, Sports Medicine, Dermatology, Cardiology, Mental Health, Pediatrics). Ensure the topic is different and specific.\n" .
            "2. Write a catchy Title (French).\n" .
            "3. Write a summary/article (approx 60 words, French).\n" .
            "4. Write an Image Prompt (English) for a photorealistic, high-quality illustrations.\n" .
            "FORMAT: Return ONLY valid JSON: {\"topic\": \"...\", \"title\": \"...\", \"content\": \"...\", \"image_prompt\": \"...\"}";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            $clean = preg_replace('/^```json|```$/m', '', trim($response['response']));
            return $clean;
        }

        return '{"title": "L\'importance de l\'hydratation", "content": "Boire de l\'eau est essentiel pour votre corps. Cela aide à maintenir votre niveau d\'énergie et favorise une peau saine. Essayez de boire 1.5L par jour.", "image_prompt": "Fresh glass of water with lemon slices, bright kitchen background, cinematic lighting, 8k"}';
    }

    public function generatePreventionPlan($age, $gender)
    {
        $systemPrompt = "You are a Senior Medical Prevention Specialist.\n" .
            "TASK: Create a 1-year Prevention Health Plan.\n" .
            "INPUT: Age: $age, Gender: $gender.\n" .
            "OUTPUT: A list of 3 to 4 priority medical checkups recommended for this profile.\n" .
            "FORMAT: JSON Array: [{\"month\": \"Month Name\", \"checkup\": \"Name of Checkup\", \"reason\": \"Short valid reason (1 sentence)\"}].\n" .
            "Language: French. Be strictly medical but reassuring.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            $clean = preg_replace('/^```json|```$/m', '', trim($response['response']));
            return $clean;
        }

        return '[{"month": "Toute l\'année", "checkup": "Consultation Générale", "reason": "Bilan annuel recommandé."}]';
    }

    public function generateMedicalReport($rawNotes, $patientName, $doctorContext = [])
    {
        $signatureBlock = "";
        if (!empty($doctorContext)) {
            $signatureBlock = "\n\nINFORMATIONS MEDECIN POUR SIGNATURE :\n" .
                "Nom : " . ($doctorContext['name'] ?? 'N/A') . "\n" .
                "Spécialité : " . ($doctorContext['specialty'] ?? 'Médecine Générale') . "\n" .
                "Adresse : " . ($doctorContext['address'] ?? 'Non spécifiée') . "\n" .
                "Date du rapport : " . ($doctorContext['date'] ?? date('d/m/Y')) . "\n";
        }

        $systemPrompt = "Tu es un Assistant Administratif Médical Expert.\n" .
            "TÂCHE : Rédige un Compte-Rendu Médical formel et structuré à partir des notes en vrac fournies par le médecin.\n" .
            "PATIENT : " . $patientName . "\n" .
            "NOTES BRUTES : " . $rawNotes . "\n" .
            $signatureBlock .
            "RÈGLES DE FORMATAGE :\n" .
            "1. Utilise un ton professionnel, objectif et médical.\n" .
            "2. Structure le document clairement avec les sections suivantes (si les infos sont disponibles) :\n" .
            "   - Motif de la consultation\n" .
            "   - Examen clinique\n" .
            "   - Diagnostic\n" .
            "   - Prescription / Traitement\n" .
            "   - Recommandations / Suivi\n" .
            "3. Corrige les fautes d'orthographe et développe les abréviations médicales courantes.\n" .
            "4. N'invente PAS d'informations non présentes dans les notes.\n" .
            "5. Langue : Français.\n" .
            "6. SIGNATURE OBLIGATOIRE : Termine le document par le bloc signature complet avec les infos médecin fournies, aligné à droite ou en bas.\n" .
            "FORMAT DE SORTIE : Retourne le texte du rapport directement, sans préambule ni balises Markdown.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']);
        }

        return "Erreur lors de la génération du rapport.";
    }

    public function generateReviewResponse($patientName, $rating, $comment, $doctorName)
    {
        $tone = ($rating >= 4) ? "reconnaissant et chaleureux" : "empathique, professionnel et orienté solution";

        $systemPrompt = "Tu es un Gestionnaire de Réputation pour un cabinet médical.\n" .
            "TÂCHE : Rédige une réponse courte et professionnelle à un avis de patient.\n" .
            "CONTEXTE :\n" .
            "- Médecin : Dr. " . $doctorName . "\n" .
            "- Patient : " . $patientName . "\n" .
            "- Note : " . $rating . "/5\n" .
            "- Commentaire : \"" . $comment . "\"\n" .
            "CONSIGNES :\n" .
            "- Ton : " . $tone . ".\n" .
            "- Sois concis (max 3 phrases).\n" .
            "- Remercie pour l'avis.\n" .
            "- Si la note est basse, excuse-toi pour le désagrément et invite à recontacter le cabinet (sans admettre de faute grave).\n" .
            "- Langue : Français.\n" .
            "FORMAT : Retourne uniquement le texte de la réponse.";

        $response = $this->callOpenRouter($systemPrompt, []);

        if (isset($response['response'])) {
            return trim($response['response']);
        }

        return "Merci pour votre retour.";
    }

    private function callOpenRouter($systemPrompt, $history)
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        // Build full message chain: System -> History
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        $data = [
            'model' => 'openai/gpt-4o-mini',
            'messages' => $messages
        ];

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . $this->siteUrl, // Required by OpenRouter
            'X-Title: Medsense Chatbot' // Optional
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => 'Erreur de connexion à l\'IA: ' . curl_error($ch)];
        }

        curl_close($ch);

        $decoded = json_decode($result, true);

        if (isset($decoded['choices'][0]['message']['content'])) {
            return ['response' => $decoded['choices'][0]['message']['content']];
        } else {
            // Log error for debugging if needed
            return ['error' => 'Réponse invalide de l\'IA', 'raw' => $result];
        }
    }
}

// Direct Handling for AJAX Requests
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Basic CORS if needed, though usually same-origin
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        try {
            $controller = new ChatController();
            $response = $controller->handleChat($_POST['message']);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        // Handle empty/test
        echo json_encode(['status' => 'ChatController Active']);
    }
}
