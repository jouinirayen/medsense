document.addEventListener('DOMContentLoaded', () => {
    const chatToggle = document.getElementById('chat-toggle');
    const chatBox = document.getElementById('chat-box');
    const closeChat = document.getElementById('close-chat');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send');
    const messagesContainer = document.getElementById('chat-messages');

    // Toggle Chat
    function toggleChat() {
        chatBox.classList.toggle('active');
        const icon = chatToggle.querySelector('i');
        if (chatBox.classList.contains('active')) {
            icon.classList.remove('fa-comments');
            icon.classList.add('fa-times');
            chatInput.focus();
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-comments');
        }
    }

    chatToggle.addEventListener('click', toggleChat);
    closeChat.addEventListener('click', () => {
        chatBox.classList.remove('active');
        chatToggle.querySelector('i').classList.remove('fa-times');
        chatToggle.querySelector('i').classList.add('fa-comments');
    });

    // Auto-resize textarea (if we switch to textarea later, keeping simple now)

    // Send Message Logic
    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        // 1. Add User Message
        appendMessage(message, 'user');
        chatInput.value = '';
        chatInput.disabled = true;
        sendBtn.disabled = true;

        // 2. Show Typing Indicator
        const typingId = showTypingIndicator();
        scrollToBottom();

        try {
            // 3. Send to Backend
            const response = await fetch('/projet2025/views/frontoffice/page-accueil/chat_handler_endpoint.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();

            // 4. Remove Typing Indicator
            removeTypingIndicator(typingId);

            // 5. Add Bot Response
            if (data.response) {
                // Try to extract JSON if present (handling cases with mixed text)
                const jsonMatch = data.response.match(/\{[\s\S]*"action"\s*:\s*"navigate"[\s\S]*\}/);

                if (jsonMatch) {
                    try {
                        const parsed = JSON.parse(jsonMatch[0]);
                        if (parsed.action === 'navigate' && parsed.url) {
                            // Valid navigation action
                            if (parsed.text) {
                                appendMessage(parsed.text, 'bot');
                            } else {
                                // If no text in JSON, use the cleaner part of the response or a default
                                const cleanText = data.response.replace(jsonMatch[0], '').trim();
                                if (cleanText) appendMessage(cleanText, 'bot');
                                else appendMessage("Je vous redirige...", 'bot');
                            }

                            // Navigate
                            setTimeout(() => {
                                window.location.href = parsed.url;
                            }, 1000);
                            return; // Stop here and don't print the raw message
                        }
                    } catch (e) {
                        console.error("JSON Parse Error:", e);
                        // If parsing fails, fall through to print raw text
                    }
                } else if (data.response.trim().startsWith('{')) {
                    // Legacy strict check just in case
                    try {
                        const parsed = JSON.parse(data.response);
                        if (parsed.action === 'navigate' && parsed.url) {
                            if (parsed.text) appendMessage(parsed.text, 'bot');
                            setTimeout(() => { window.location.href = parsed.url; }, 1000);
                            return;
                        }
                    } catch (e) { }
                }

                // Fallback: print as text
                appendMessage(data.response, 'bot');
            } else if (data.error) {
                appendMessage("Désolé, je rencontre un problème de connexion.", 'bot');
                console.error(data.error);
            }
        } catch (error) {
            removeTypingIndicator(typingId);
            appendMessage("Désolé, je n'arrive pas à joindre le serveur.", 'bot');
            console.error('Error:', error);
        } finally {
            chatInput.disabled = false;
            sendBtn.disabled = false;
            chatInput.focus();
            scrollToBottom();
        }
    }

    // Event Listeners for sending
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Helper: Append Message
    function appendMessage(text, sender) {
        const div = document.createElement('div');
        div.classList.add('message', sender);

        // Convert simple markdown-like newlines to <br> if needed, 
        // but textContent is safer against XSS. 
        // Let's use innerText which preserves newlines visually in standard divs usually,
        // or ensure CSS handles white-space: pre-wrap;
        div.textContent = text;

        messagesContainer.appendChild(div);
        scrollToBottom();
    }

    // Helper: Show Typing
    function showTypingIndicator() {
        const id = 'typing-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'typing-indicator';
        div.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        messagesContainer.appendChild(div);
        return id;
    }

    // Helper: Remove Typing
    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
