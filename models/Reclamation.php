<?php
require_once '../../../config/config.php';

class Reclamation
{
    const TYPE_NORMAL = 'normal';
    const TYPE_URGENCE = 'urgence';

    const STATUS_OPEN = 'ouvert';
    const STATUS_IN_PROGRESS = 'en cours';
    const STATUS_CLOSED = 'fermé';

    private $pdo;
    private $id;
    private $titre;
    private $description;
    private $date;
    private $id_user;
    private $type;
    private $statut;

    public function __construct()
    {
        $this->pdo = (new config())->getConnexion();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->id_user;
    }

    public function setUserId(int $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, [self::TYPE_NORMAL, self::TYPE_URGENCE])) {
            throw new InvalidArgumentException("Type de réclamation invalide");
        }
        $this->type = $type;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        if (!in_array($statut, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_CLOSED])) {
            throw new InvalidArgumentException("Statut invalide");
        }
        $this->statut = $statut;
        return $this;
    }

    // Hydrate from array
    public function hydrate(array $data): self
    {
        if (isset($data['id'])) {
            $this->setId((int)$data['id']);
        }
        if (isset($data['titre'])) {
            $this->setTitre($data['titre']);
        }
        if (isset($data['description'])) {
            $this->setDescription($data['description']);
        }
        if (isset($data['date'])) {
            $this->setDate($data['date']);
        }
        if (isset($data['id_user'])) {
            $this->setUserId((int)$data['id_user']);
        }
        if (isset($data['type'])) {
            $this->setType($data['type']);
        }
        if (isset($data['statut'])) {
            $this->setStatut($data['statut']);
        }
        return $this;
    }

    // Convert to array
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'titre' => $this->getTitre(),
            'description' => $this->getDescription(),
            'date' => $this->getDate(),
            'id_user' => $this->getUserId(),
            'type' => $this->getType(),
            'statut' => $this->getStatut()
        ];
    }

    // CRUD Methods
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reclamation WHERE id_user = ? ORDER BY date DESC");
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        
        $reclamations = [];
        foreach ($results as $data) {
            $reclamation = new self();
            $reclamation->hydrate($data);
            $reclamations[] = $reclamation;
        }
        return $reclamations;
    }

    public function findForUser(int $id, int $userId): ?self
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reclamation WHERE id = ? AND id_user = ?");
        $stmt->execute([$id, $userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $reclamation = new self();
            $reclamation->hydrate($result);
            return $reclamation;
        }
        return null;
    }

    public function create(): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO reclamation (titre, description, date, id_user, type, statut) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $this->titre,
            $this->description,
            $this->date,
            $this->id_user,
            $this->type,
            $this->statut
        ]);
    }

    public function update(): bool
    {
        if (!$this->id) {
            throw new Exception("Cannot update without ID");
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE reclamation 
            SET titre = ?, description = ?, type = ?, statut = ? 
            WHERE id = ? AND id_user = ?
        ");
        
        return $stmt->execute([
            $this->titre,
            $this->description,
            $this->type,
            $this->statut,
            $this->id,
            $this->id_user
        ]);
    }

    /**
     * Met à jour uniquement le statut d'une réclamation (pour admin)
     * Ne vérifie pas l'id_user, permet à l'admin de modifier n'importe quelle réclamation
     */
    public function updateStatut(): bool
    {
        if (!$this->id) {
            throw new Exception("Cannot update statut without ID");
        }
        
        if (!$this->statut) {
            throw new Exception("Statut is required");
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE reclamation 
            SET statut = ? 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $this->statut,
            $this->id
        ]);
    }

    public function delete(): bool
    {
        if (!$this->id) {
            throw new Exception("Cannot delete without ID");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM reclamation WHERE id = ? AND id_user = ?");
        return $stmt->execute([$this->id, $this->id_user]);
    }

    public function deleteForUser(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM reclamation WHERE id = ? AND id_user = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function generateDetailedDescription(string $titre, string $description): string
    {
        // Normaliser le texte
        $titre = trim($titre);
        $description = trim($description);
        
        // Extraire les mots-clés importants
        $keywords = $this->extractKeywords($titre . ' ' . $description);
        
        // Détecter le contexte et le type de problème
        $context = $this->detectContext($titre, $description);
        
        // Générer la description enrichie
        $detailedDescription = $description;
        
        // Ajouter un préambule contextuel si la description est courte
        if (strlen($description) < 100) {
            $preambule = $this->generateContextualPreambule($context, $keywords);
            $detailedDescription = $preambule . "\n\n" . $description;
        }
        
        // Enrichir avec des détails supplémentaires basés sur les mots-clés
        $enrichments = $this->generateEnrichments($keywords, $context);
        if (!empty($enrichments)) {
            $detailedDescription .= "\n\n" . $enrichments;
        }
        
        // Ajouter des suggestions de résolution si applicable
        $suggestions = $this->generateSuggestions($context, $keywords);
        if (!empty($suggestions)) {
            $detailedDescription .= "\n\n--- Suggestions de résolution ---\n" . $suggestions;
        }
        
        return trim($detailedDescription);
    }

    /**
     * Extrait les mots-clés importants du texte
     */
    private function extractKeywords(string $text): array
    {
        // Mots vides à ignorer
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'mais', 'pour', 'avec', 'sans', 'sur', 'dans', 'par', 'est', 'sont', 'a', 'ont', 'être', 'avoir', 'faire', 'ce', 'cette', 'ces', 'mon', 'ma', 'mes', 'votre', 'vos', 'notre', 'nos'];
        
        // Nettoyer et normaliser le texte
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text);
        
        // Filtrer les mots vides et les mots courts
        $keywords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }
        
        // Compter les occurrences et retourner les plus fréquents
        $wordCount = array_count_values($keywords);
        arsort($wordCount);
        return array_slice(array_keys($wordCount), 0, 10);
    }

    /**
     * Détecte le contexte du problème
     */
    private function detectContext(string $titre, string $description): string
    {
        $text = mb_strtolower($titre . ' ' . $description, 'UTF-8');
        
        $contexts = [
            'technique' => ['bug', 'erreur', 'crash', 'plantage', 'fonctionne pas', 'ne marche pas', 'problème technique', 'application', 'site', 'système'],
            'compte' => ['compte', 'connexion', 'mot de passe', 'identifiant', 'login', 'authentification', 'accès'],
            'paiement' => ['paiement', 'facture', 'facturation', 'tarif', 'prix', 'coût', 'abonnement', 'remboursement'],
            'service' => ['service', 'rendez-vous', 'consultation', 'médecin', 'docteur', 'patient', 'soin', 'traitement'],
            'urgence' => ['urgence', 'urgent', 'immédiat', 'rapide', 'critique', 'important', 'prioritaire'],
            'information' => ['information', 'question', 'demande', 'renseignement', 'aide', 'support']
        ];
        
        $scores = [];
        foreach ($contexts as $context => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            $scores[$context] = $score;
        }
        
        $maxScore = max($scores);
        if ($maxScore > 0) {
            return array_search($maxScore, $scores);
        }
        
        return 'general';
    }

    /**
     * Génère un préambule contextuel
     */
    private function generateContextualPreambule(string $context, array $keywords): string
    {
        $preambules = [
            'technique' => "Problème technique identifié : ",
            'compte' => "Problème lié au compte utilisateur : ",
            'paiement' => "Question concernant la facturation : ",
            'service' => "Demande relative au service : ",
            'urgence' => "⚠️ URGENCE - Situation nécessitant une attention immédiate : ",
            'information' => "Demande d'information : ",
            'general' => "Réclamation concernant : "
        ];
        
        $preambule = $preambules[$context] ?? $preambules['general'];
        $keywordsStr = implode(', ', array_slice($keywords, 0, 3));
        
        return $preambule . $keywordsStr;
    }

    /**
     * Génère des enrichissements basés sur les mots-clés
     */
    private function generateEnrichments(array $keywords, string $context): string
    {
        $enrichments = [];
        
        // Enrichissements contextuels
        if ($context === 'technique') {
            $enrichments[] = "Détails techniques supplémentaires :";
            $enrichments[] = "- Navigateur/Système utilisé : À préciser";
            $enrichments[] = "- Étapes pour reproduire le problème : À détailler";
            $enrichments[] = "- Message d'erreur exact : À fournir";
        } elseif ($context === 'compte') {
            $enrichments[] = "Informations complémentaires :";
            $enrichments[] = "- Date de dernière connexion réussie : À préciser";
            $enrichments[] = "- Tentatives de réinitialisation : À indiquer";
        } elseif ($context === 'service') {
            $enrichments[] = "Informations sur le service :";
            $enrichments[] = "- Date souhaitée : À préciser";
            $enrichments[] = "- Type de consultation : À spécifier";
        }
        
        return implode("\n", $enrichments);
    }

    /**
     * Génère des suggestions de résolution
     */
    private function generateSuggestions(string $context, array $keywords): string
    {
        $suggestions = [];
        
        if ($context === 'technique') {
            $suggestions[] = "1. Vérifier la connexion internet";
            $suggestions[] = "2. Vider le cache du navigateur";
            $suggestions[] = "3. Essayer avec un autre navigateur";
            $suggestions[] = "4. Contacter le support technique avec les détails ci-dessus";
        } elseif ($context === 'compte') {
            $suggestions[] = "1. Utiliser la fonction 'Mot de passe oublié'";
            $suggestions[] = "2. Vérifier l'adresse email associée";
            $suggestions[] = "3. Contacter le support pour réinitialisation";
        } elseif ($context === 'service') {
            $suggestions[] = "1. Vérifier les disponibilités en ligne";
            $suggestions[] = "2. Contacter directement le service concerné";
            $suggestions[] = "3. Consulter la FAQ pour plus d'informations";
        }
        
        return implode("\n", $suggestions);
    }

    /**
     * Fonction 2: Détecte les mauvais mots dans le titre et la description
     * Retourne un tableau avec les mots détectés ou null si aucun mot inapproprié
     * Améliorée pour détecter uniquement les mots complets, pas les sous-chaînes
     */
    public function detectBadWords(string $titre, string $description): ?array
    {
        // Liste de mots inappropriés (en français) - seulement les mots vraiment offensants
        // Retiré les mots qui peuvent faire partie de mots légitimes
        $badWords = [
            'putain', 'connard', 'salope', 'enculé', 'enculer',
            'foutre', 'bordel', 'chier', 'chié', 'nique', 'niquer',
            'bite', 'couilles', 'pute', 'putes', 'salaud', 'salauds',
            'connasse', 'fdp', 'ntm' // Abréviations vraiment offensantes
        ];
        
        // Normaliser le texte
        $text = mb_strtolower($titre . ' ' . $description, 'UTF-8');
        
        // Nettoyer le texte mais garder la ponctuation pour les limites de mots
        // Remplacer la ponctuation par des espaces pour créer des limites de mots
        $text = preg_replace('/[^\p{L}\p{N}]/u', ' ', $text);
        // Normaliser les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Diviser en mots
        $words = explode(' ', $text);
        
        // Détecter les mots inappropriés - seulement correspondance exacte
        $detectedWords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            
            // Vérification exacte (insensible à la casse)
            foreach ($badWords as $badWord) {
                if (mb_strtolower($word, 'UTF-8') === mb_strtolower($badWord, 'UTF-8')) {
                    if (!in_array($badWord, $detectedWords)) {
                        $detectedWords[] = $badWord;
                    }
                }
            }
        }
        
        // Vérifier aussi les mots avec accents et variations
        // Utiliser des limites de mots pour éviter les faux positifs
        $textOriginal = mb_strtolower($titre . ' ' . $description, 'UTF-8');
        foreach ($badWords as $badWord) {
            // Utiliser des limites de mots avec regex pour éviter les sous-chaînes
            $pattern = '/\b' . preg_quote($badWord, '/') . '\b/u';
            if (preg_match($pattern, $textOriginal) && !in_array($badWord, $detectedWords)) {
                $detectedWords[] = $badWord;
            }
        }
        
        return !empty($detectedWords) ? $detectedWords : null;
    }

    /**
     * Fonction 3: Collecte les statistiques des utilisateurs et réclamations
     */
    public function getStatistics(): array
    {
        $stats = [];
        
        // Statistiques générales des réclamations
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM reclamation");
        $stats['total_reclamations'] = (int)$stmt->fetch()['total'];
        
        // Réclamations par type
        $stmt = $this->pdo->query("SELECT type, COUNT(*) as count FROM reclamation GROUP BY type");
        $stats['by_type'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_type'][$row['type']] = (int)$row['count'];
        }
        
        // Réclamations par statut
        $stmt = $this->pdo->query("SELECT statut, COUNT(*) as count FROM reclamation GROUP BY statut");
        $stats['by_status'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_status'][$row['statut']] = (int)$row['count'];
        }
        
        // Réclamations par mois (derniers 6 mois)
        $stmt = $this->pdo->query("
            SELECT DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count 
            FROM reclamation 
            WHERE date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month 
            ORDER BY month DESC
        ");
        $stats['by_month'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_month'][$row['month']] = (int)$row['count'];
        }
        
        // Statistiques par utilisateur
        $stmt = $this->pdo->query("
            SELECT u.id, u.username, u.email, 
                   COUNT(r.id) as total_reclamations,
                   SUM(CASE WHEN r.type = 'urgence' THEN 1 ELSE 0 END) as urgences,
                   SUM(CASE WHEN r.statut = 'ouvert' THEN 1 ELSE 0 END) as ouvertes,
                   SUM(CASE WHEN r.statut = 'fermé' THEN 1 ELSE 0 END) as fermees
            FROM user u
            LEFT JOIN reclamation r ON u.id = r.id_user
            GROUP BY u.id, u.username, u.email
            ORDER BY total_reclamations DESC
        ");
        $stats['by_user'] = [];
        while ($row = $stmt->fetch()) {
            $stats['by_user'][] = [
                'user_id' => (int)$row['id'],
                'username' => $row['username'],
                'email' => $row['email'],
                'total_reclamations' => (int)$row['total_reclamations'],
                'urgences' => (int)$row['urgences'],
                'ouvertes' => (int)$row['ouvertes'],
                'fermees' => (int)$row['fermees']
            ];
        }
        
        // Réclamations récentes (dernières 24h)
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM reclamation 
            WHERE date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stats['recent_24h'] = (int)$stmt->fetch()['count'];
        
        // Réclamations cette semaine
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM reclamation 
            WHERE date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['this_week'] = (int)$stmt->fetch()['count'];
        
        // Réclamations ce mois
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM reclamation 
            WHERE MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())
        ");
        $stats['this_month'] = (int)$stmt->fetch()['count'];
        
        // Temps moyen de résolution (pour les réclamations fermées)
        // Note: Utilisation de la date actuelle comme approximation si pas de colonne updated_at
        $stmt = $this->pdo->query("
            SELECT AVG(DATEDIFF(NOW(), date)) as avg_days
            FROM reclamation 
            WHERE statut = 'fermé'
        ");
        $result = $stmt->fetch();
        $stats['avg_resolution_days'] = $result['avg_days'] ? round((float)$result['avg_days'], 1) : 0;
        
        return $stats;
    }

    /**
     * Getter pour les statistiques (méthode statique pour faciliter l'accès)
     */
    public static function getStatisticsStatic(): array
    {
        $instance = new self();
        return $instance->getStatistics();
    }
}