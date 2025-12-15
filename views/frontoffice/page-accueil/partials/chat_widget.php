<?php
// views/frontoffice/partials/chat_widget.php
?>
<div class="chat-widget-container">
    <!-- Chat Toggle Button -->
    <button id="chat-toggle" class="chat-toggle-btn" style="padding: 0; overflow: hidden; border: 2px solid white;">
        <img src="/projet_unifie/views/images/chatbot_icon_3d.png?v=<?php echo time(); ?>" alt="Chat"
            style="width: 100%; height: 100%; object-fit: cover;">
    </button>

    <!-- Chat Box -->
    <div id="chat-box" class="chat-box">
        <div class="chat-header">
            <div class="chat-title">
                <h3>Assistant Medsense</h3>
                <p>En ligne</p>
            </div>
            <button id="close-chat" class="chat-close">
                <i class="fas fa-minus"></i>
            </button>
        </div>

        <div id="chat-messages" class="chat-messages">
            <div class="message bot">
                Bonjour ! Je suis l'assistant virtuel de Medsense. Comment puis-je vous aider aujourd'hui ?
            </div>
        </div>

        <div class="chat-input-area">
            <input type="text" id="chat-input" class="chat-input" placeholder="Écrivez ou parlez...">

            <button id="chat-send" class="chat-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>