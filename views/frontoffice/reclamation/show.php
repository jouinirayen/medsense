<?php
require_once '../../../config/config.php';
require_once '../../../models/Reclamation.php';
require_once '../../../models/Response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get reclamation ID from URL
$id = $_GET['id'] ?? null;
$userId = 1; // Hardcoded user ID

if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch reclamation and responses
$reclamationModel = new Reclamation();
$responseModel = new Response();

$reclamation = $reclamationModel->findForUser($id, $userId);
$responses = $responseModel->forReclamation($id);

if (!$reclamation) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Détails de la Réclamation";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<?php include '../../../navbar.php'; ?>
<main class="main-content">
    <div class="container">
        <div class="header-section">
            <a href="index.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i>
                Retour à la liste
            </a>
            <h1>Détails de la Réclamation</h1>
        </div>

        <div class="card reclamation-details">
            <div class="reclamation-header">
                <h2><?= htmlspecialchars($reclamation->getTitre()); ?></h2>
                <div class="reclamation-meta">
                    <span class="badge badge-<?= $reclamation->getType() === 'urgence' ? 'urgence' : 'normal'; ?>">
                        <?= htmlspecialchars($reclamation->getType()); ?>
                    </span>
                    <span class="badge badge-statut statut-<?= str_replace(' ', '-', $reclamation->getStatut()); ?>">
                        <?= htmlspecialchars($reclamation->getStatut()); ?>
                    </span>
                    <span class="date">
                        <i class="far fa-calendar"></i>
                        <?= date('d/m/Y H:i', strtotime($reclamation->getDate())); ?>
                    </span>
                </div>
            </div>

            <div class="reclamation-content">
                <h3>Description</h3>
                <div class="description-box">
                    <?= nl2br(htmlspecialchars($reclamation->getDescription())); ?>
                </div>
            </div>
        </div>

        <div class="responses-section">
            <div class="responses-header">
                <h3>
                    <i class="fas fa-comments"></i>
                    Réponses (<?= count($responses); ?>)
                </h3>
            </div>

            <?php if (!empty($responses)): ?>
                <div class="responses-list">
                    <?php foreach ($responses as $response): ?>
                        <?php if ($response instanceof Response): ?>
                            <div class="response-card">
                                <div class="response-header">
                                    <div class="response-author">
                                        <i class="fas fa-user"></i>
                                        <?= htmlspecialchars($response->getUsername() ?? 'Administrateur'); ?>
                                    </div>
                                    <div class="response-date">
                                        <i class="far fa-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($response->getDate())); ?>
                                    </div>
                                </div>
                                <div class="response-content">
                                    <?= nl2br(htmlspecialchars($response->getContenu())); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <h4>Aucune réponse pour cette réclamation</h4>
                    <p>Les réponses de l'administration apparaîtront ici.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chatbot Section -->
        <div class="chatbot-section">
            <div class="chatbot-header" onclick="toggleChatbot()">
                <div class="chatbot-header-content">
                    <div class="chatbot-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="chatbot-info">
                        <h3>Assistant Virtuel</h3>
                        <p>Posez vos questions sur votre réclamation</p>
                    </div>
                    <div class="chatbot-status">
                        <span class="status-dot"></span>
                        <span>En ligne</span>
                    </div>
                </div>
                <button class="chatbot-toggle" id="chatbotToggle">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>

            <div class="chatbot-container" id="chatbotContainer" style="display: none;">
                <div class="chatbot-messages" id="chatbotMessages">
                    <div class="message bot-message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <p>Bonjour ! Je suis votre assistant virtuel. Je peux vous aider à obtenir des informations sur votre réclamation.</p>
                            <p><strong>Voici quelques questions que je peux répondre :</strong></p>
                            <ul class="suggested-questions">
                                <li onclick="askQuestion('Quel est le statut de ma réclamation ?')">Quel est le statut de ma réclamation ?</li>
                                <li onclick="askQuestion('Combien de temps cela prend-il pour être traité ?')">Combien de temps cela prend-il pour être traité ?</li>
                                <li onclick="askQuestion('Quelles sont les prochaines étapes ?')">Quelles sont les prochaines étapes ?</li>
                                <li onclick="askQuestion('Comment puis-je suivre ma réclamation ?')">Comment puis-je suivre ma réclamation ?</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="chatbot-input-container">
                    <input 
                        type="text" 
                        id="chatbotInput" 
                        class="chatbot-input" 
                        placeholder="Tapez votre question ici..."
                        onkeypress="handleChatbotKeyPress(event)"
                    >
                    <button class="chatbot-send-btn" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .container {
        max-width: 900px;
        margin: 0 auto;
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-section h1 {
        color: #1f2937;
        font-size: 2rem;
        margin: 0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 10px 20px;
        background: #6b7280;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }

    .reclamation-details {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        margin-bottom: 2rem;
        border-left: 4px solid #3b82f6;
    }

    .reclamation-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .reclamation-header h2 {
        color: #1f2937;
        font-size: 1.5rem;
        margin: 0;
        flex: 1;
        min-width: 300px;
    }

    .reclamation-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-normal {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-urgence {
        background: #fecaca;
        color: #dc2626;
    }

    .badge-statut {
        background: #dcfce7;
        color: #166534;
    }

    .statut-ouvert {
        background: #fef3c7;
        color: #92400e;
    }

    .statut-en-cours {
        background: #dbeafe;
        color: #1e40af;
    }

    .statut-fermé {
        background: #dcfce7;
        color: #166534;
    }

    .date {
        color: #6b7280;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .reclamation-content h3 {
        color: #374151;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .description-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        line-height: 1.6;
        color: #4b5563;
    }

    .responses-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .responses-header {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .responses-header h3 {
        color: #1f2937;
        font-size: 1.3rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .responses-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .response-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .response-card:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .response-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .response-author {
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .response-date {
        color: #6b7280;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .response-content {
        line-height: 1.6;
        color: #4b5563;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state h4 {
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        color: #374151;
    }

    @media (max-width: 768px) {
        .header-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-section h1 {
            font-size: 1.5rem;
        }

        .reclamation-header {
            flex-direction: column;
        }

        .reclamation-header h2 {
            min-width: auto;
        }

        .reclamation-meta {
            justify-content: flex-start;
        }

        .response-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 1rem;
        }

        .reclamation-details,
        .responses-section {
            padding: 1rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
    }

    /* Chatbot Styles */
    .chatbot-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
        overflow: hidden;
    }

    .chatbot-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .chatbot-header:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    .chatbot-header-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .chatbot-avatar {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .chatbot-info {
        flex: 1;
    }

    .chatbot-info h3 {
        margin: 0 0 0.25rem 0;
        font-size: 1.2rem;
    }

    .chatbot-info p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .chatbot-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .chatbot-toggle {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
        margin-left: auto;
    }

    .chatbot-toggle:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .chatbot-toggle.rotated {
        transform: rotate(180deg);
    }

    .chatbot-container {
        max-height: 500px;
        display: flex;
        flex-direction: column;
    }

    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        max-height: 400px;
        background: #f8fafc;
    }

    .message {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .bot-message .message-avatar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .user-message {
        flex-direction: row-reverse;
    }

    .user-message .message-avatar {
        background: #3b82f6;
        color: white;
    }

    .message-content {
        flex: 1;
        background: white;
        padding: 1rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .user-message .message-content {
        background: #3b82f6;
        color: white;
    }

    .message-content p {
        margin: 0.5rem 0;
        line-height: 1.6;
    }

    .message-content ul {
        margin: 0.5rem 0;
        padding-left: 1.5rem;
    }

    .suggested-questions {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0 0;
    }

    .suggested-questions li {
        background: #e0e7ff;
        color: #4f46e5;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .suggested-questions li:hover {
        background: #c7d2fe;
        transform: translateX(5px);
    }

    .chatbot-input-container {
        display: flex;
        gap: 0.5rem;
        padding: 1rem;
        background: white;
        border-top: 1px solid #e5e7eb;
    }

    .chatbot-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 25px;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.3s ease;
    }

    .chatbot-input:focus {
        border-color: #667eea;
    }

    .chatbot-send-btn {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }

    .chatbot-send-btn:hover {
        transform: scale(1.1);
    }

    .chatbot-send-btn:active {
        transform: scale(0.95);
    }

    .typing-indicator {
        display: flex;
        gap: 0.5rem;
        padding: 1rem;
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        background: #667eea;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.7;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .chatbot-header-content {
            flex-wrap: wrap;
        }

        .chatbot-status {
            width: 100%;
            margin-top: 0.5rem;
        }

        .chatbot-messages {
            max-height: 300px;
        }
    }
</style>

<script>
    // Données de la réclamation pour le chatbot
    const reclamationData = {
        id: <?= $reclamation->getId() ?>,
        titre: <?= json_encode($reclamation->getTitre()) ?>,
        statut: <?= json_encode($reclamation->getStatut()) ?>,
        type: <?= json_encode($reclamation->getType()) ?>,
        date: <?= json_encode($reclamation->getDate()) ?>,
        responsesCount: <?= count($responses) ?>
    };

    function toggleChatbot() {
        const container = document.getElementById('chatbotContainer');
        const toggle = document.getElementById('chatbotToggle');
        const isVisible = container.style.display !== 'none';
        
        container.style.display = isVisible ? 'none' : 'flex';
        toggle.classList.toggle('rotated', !isVisible);
    }

    function handleChatbotKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function askQuestion(question) {
        document.getElementById('chatbotInput').value = question;
        sendMessage();
    }

    function sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();
        
        if (!message) return;

        // Afficher le message de l'utilisateur
        addMessage(message, 'user');
        input.value = '';

        // Afficher l'indicateur de frappe
        showTypingIndicator();

        // Simuler un délai de réponse
        setTimeout(() => {
            hideTypingIndicator();
            const response = getChatbotResponse(message);
            addMessage(response, 'bot');
        }, 1000 + Math.random() * 1000);
    }

    function addMessage(text, type) {
        const messagesContainer = document.getElementById('chatbotMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}-message`;
        
        const avatar = type === 'bot' 
            ? '<i class="fas fa-robot"></i>' 
            : '<i class="fas fa-user"></i>';
        
        let content = text;
        if (type === 'bot' && text.includes('suggestions')) {
            content = formatBotMessage(text);
        } else {
            content = `<p>${escapeHtml(text)}</p>`;
        }
        
        messageDiv.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="message-content">${content}</div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function formatBotMessage(text) {
        // Formater les messages avec des listes ou des suggestions
        return `<p>${escapeHtml(text)}</p>`;
    }

    function showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbotMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot-message';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function hideTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) {
            indicator.remove();
        }
    }

    function getChatbotResponse(question) {
        const q = question.toLowerCase();
        
        // Questions sur le statut
        if (q.includes('statut') || q.includes('état') || q.includes('status')) {
            const statutMessages = {
                'ouvert': 'Votre réclamation est actuellement <strong>ouverte</strong> et en attente de traitement par notre équipe. Nous allons l\'examiner dans les plus brefs délais.',
                'en cours': 'Votre réclamation est <strong>en cours de traitement</strong>. Notre équipe travaille activement sur votre demande.',
                'fermé': 'Votre réclamation est <strong>fermée</strong>. Le problème a été résolu. Si vous avez d\'autres questions, n\'hésitez pas à créer une nouvelle réclamation.'
            };
            return statutMessages[reclamationData.statut] || `Le statut actuel de votre réclamation est : <strong>${reclamationData.statut}</strong>.`;
        }

        // Questions sur le temps de traitement
        if (q.includes('temps') || q.includes('délai') || q.includes('combien') || q.includes('durée')) {
            const daysSince = Math.floor((new Date() - new Date(reclamationData.date)) / (1000 * 60 * 60 * 24));
            if (reclamationData.statut === 'ouvert') {
                return `Votre réclamation a été créée il y a ${daysSince} jour(s). Les réclamations normales sont généralement traitées sous 2-5 jours ouvrés. Les réclamations urgentes sont traitées en priorité, généralement sous 24 heures.`;
            } else if (reclamationData.statut === 'en cours') {
                return `Votre réclamation est en cours de traitement depuis ${daysSince} jour(s). Notre équipe travaille activement dessus. Vous recevrez une réponse dès que possible.`;
            } else {
                return `Votre réclamation a été résolue. Le traitement a pris ${daysSince} jour(s).`;
            }
        }

        // Questions sur les prochaines étapes
        if (q.includes('prochaine') || q.includes('étape') || q.includes('suivant') || q.includes('que faire')) {
            if (reclamationData.statut === 'ouvert') {
                return 'Les prochaines étapes :<br>1. Notre équipe va examiner votre réclamation<br>2. Vous recevrez une réponse dans les 2-5 jours ouvrés<br>3. Si nécessaire, nous vous contacterons pour plus d\'informations<br>4. Une fois résolue, votre réclamation sera marquée comme fermée';
            } else if (reclamationData.statut === 'en cours') {
                return 'Votre réclamation est actuellement en cours de traitement. Notre équipe travaille dessus. Vous recevrez une mise à jour dès que possible. En attendant, vous pouvez consulter les réponses ci-dessus.';
            } else {
                return 'Votre réclamation est fermée. Si vous avez besoin d\'aide supplémentaire, vous pouvez créer une nouvelle réclamation.';
            }
        }

        // Questions sur le suivi
        if (q.includes('suivre') || q.includes('suivi') || q.includes('suivre ma réclamation')) {
            return `Vous pouvez suivre votre réclamation de plusieurs façons :<br>1. Consultez cette page régulièrement pour voir les mises à jour<br>2. Vérifiez le statut actuel : <strong>${reclamationData.statut}</strong><br>3. Consultez les réponses de l'administration ci-dessus (${reclamationData.responsesCount} réponse(s))<br>4. Vous recevrez des notifications par email si configuré`;
        }

        // Questions sur le type
        if (q.includes('type') || q.includes('urgence') || q.includes('normal')) {
            const typeMessage = reclamationData.type === 'urgence' 
                ? 'Votre réclamation est de type <strong>URGENCE</strong>. Elle est traitée en priorité et devrait recevoir une réponse sous 24 heures.'
                : 'Votre réclamation est de type <strong>NORMAL</strong>. Elle sera traitée dans l\'ordre d\'arrivée, généralement sous 2-5 jours ouvrés.';
            return typeMessage;
        }

        // Questions sur les réponses
        if (q.includes('réponse') || q.includes('réponses') || q.includes('commentaire')) {
            if (reclamationData.responsesCount > 0) {
                return `Vous avez ${reclamationData.responsesCount} réponse(s) de l'administration. Consultez la section "Réponses" ci-dessus pour voir les détails.`;
            } else {
                return 'Vous n\'avez pas encore de réponse de l\'administration. Notre équipe travaille sur votre réclamation et vous répondra dès que possible.';
            }
        }

        // Questions générales
        if (q.includes('bonjour') || q.includes('salut') || q.includes('hello') || q.includes('bonsoir')) {
            return 'Bonjour ! Je suis là pour vous aider avec votre réclamation. Posez-moi une question sur le statut, les délais, ou les prochaines étapes.';
        }

        if (q.includes('aide') || q.includes('help') || q.includes('assistance')) {
            return 'Je peux vous aider avec :<br>• Le statut de votre réclamation<br>• Les délais de traitement<br>• Les prochaines étapes<br>• Comment suivre votre réclamation<br>• Les informations sur le type de réclamation<br>Posez-moi une question spécifique !';
        }

        // Réponse par défaut
        return `Je comprends votre question. Concernant votre réclamation "${reclamationData.titre}", voici les informations actuelles :<br>• Statut : <strong>${reclamationData.statut}</strong><br>• Type : <strong>${reclamationData.type}</strong><br>• Réponses reçues : ${reclamationData.responsesCount}<br><br>Pour des questions plus spécifiques, vous pouvez demander : "Quel est le statut ?", "Combien de temps cela prend-il ?", ou "Quelles sont les prochaines étapes ?"`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<?php include '../../../footer.php'; ?>
</body>
</html>