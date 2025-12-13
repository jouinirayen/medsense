/**
 * Siri UI Controller
 * Manages the visual state of the floating bubble based on VoiceAssistant events.
 */
class SiriController {
    constructor() {
        this.widget = document.getElementById('siri-widget');
        this.statusText = document.getElementById('siri-status');
        this.assistant = window.VoiceAssistant;
        this.isActive = false;
    }

    toggle() {
        if (this.isActive) {
            this.stop();
        } else {
            this.start();
        }
    }

    start() {
        if (!this.assistant) return;
        this.isActive = true;
        this.setVisualState('standby', 'Je vous écoute...');
        this.startListeningCycle();
    }

    stop() {
        this.isActive = false;
        if (this.assistant) this.assistant.stop();

        // Visual Reset
        this.setVisualState('standby', 'Appuyez pour parler');
        this.widget.classList.remove('listening', 'speaking');
    }

    startListeningCycle() {
        if (!this.isActive) return;

        this.setVisualState('listening', 'Écoute en cours...');

        this.assistant.startListening(
            (text) => {
                if (!this.isActive) return;

                this.handleUserCommand(text);
            },
            () => {
                // On End (Silence)
            }
        );
    }

    handleUserCommand(text) {
        if (!this.isActive) return;

        this.setVisualState('speaking', 'Analyse...');

        fetch('/projet2025/views/frontoffice/page-accueil/chat_handler_endpoint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
            .then(res => res.json())
            .then(data => {
                if (!this.isActive) return;

                if (data.response) {
                    let responseText = data.response;
                    let navigateUrl = null;

                    // Parse JSON if present (strip markdown if needed)
                    let cleanResponse = responseText.replace(/^```json/, '').replace(/```$/, '').trim();

                    if (cleanResponse.startsWith('{') || cleanResponse.startsWith('"{')) {
                        try {
                            const cmd = JSON.parse(cleanResponse);
                            if (cmd.action === 'navigate') {
                                responseText = cmd.text;
                                navigateUrl = cmd.url;
                            }
                        } catch (e) {
                            // console.log("Not JSON command, treating as text");
                        }
                    }

                    this.setVisualState('speaking', responseText);

                    this.assistant.speak(responseText, () => {
                        if (!this.isActive) return;

                        // Execute Navigation
                        if (navigateUrl) {
                            this.setVisualState('standby', 'Redirection...');
                            // console.log("Navigating to:", navigateUrl);
                            window.location.href = navigateUrl;
                            return;
                        }

                        // Loop back to listening
                        // console.log("Restarting listener.");
                        this.setVisualState('listening', 'Je vous écoute...');

                        setTimeout(() => {
                            this.startListeningCycle();
                        }, 200);
                    });
                } else {
                    this.setVisualState('standby', 'Pas de réponse.');
                }
            })
            .catch(err => {
                console.error("Siri Error:", err);
                this.setVisualState('standby', 'Erreur réseau');
            });
    }

    setVisualState(state, text) {
        // Reset classes
        this.widget.classList.remove('standby', 'listening', 'speaking');

        // Add new state
        this.widget.classList.add(state);

        // Update text
        if (text) this.statusText.innerText = (text.length > 30) ? "En train de parler..." : text;
    }
}

// Init when loaded
window.addEventListener('load', () => {
    window.SiriController = new SiriController();
});
