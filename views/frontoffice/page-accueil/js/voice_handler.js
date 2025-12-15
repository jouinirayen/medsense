/**
 * Professional Voice Handler for Medsense
 * Uses Web Speech API (Google's Native Engine) for Zero-Latency performance.
 */
class VoiceAssistant {
    constructor() {
        this.synthesis = window.speechSynthesis;
        this.recognition = this.initRecognition();
        this.isListening = false;
        this.preferredVoice = null;

        // Initialize voices when they are ready
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = () => this.loadBestVoice();
        }
        this.loadBestVoice();
    }

    /**
     * Initialize STT (Speech to Text)
     * Uses Chrome's native Google Speech API
     */
    initRecognition() {
        if ('webkitSpeechRecognition' in window) {
            const recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'fr-FR';
            return recognition;
        }
        console.warn("Web Speech API not supported in this browser.");
        return null;
    }

    /**
     * Finds the most natural 'Human' voice available
     * Priority: Google Online > Microsoft Online > Native
     */
    loadBestVoice() {
        const voices = this.synthesis.getVoices();
        // 1. Try to find Google's high-quality French voice
        this.preferredVoice = voices.find(v => v.name.includes('Google') && v.lang.includes('fr'));

        // 2. Fallback to Microsoft's high-quality French voice
        if (!this.preferredVoice) {
            this.preferredVoice = voices.find(v => v.name.includes('Microsoft') && v.lang.includes('fr'));
        }

        // 3. Fallback to any French voice
        if (!this.preferredVoice) {
            this.preferredVoice = voices.find(v => v.lang.includes('fr'));
        }

        console.log("Medsense Voice loaded:", this.preferredVoice ? this.preferredVoice.name : "Default");
    }

    /**
     * Starts listening for user input
     * @param {Function} onResult - Callback with text (string)
     * @param {Function} onEnd - Callback when listening stops
     */
    startListening(onResult, onEnd) {
        if (!this.recognition) return;

        // Stop any current speech
        this.synthesis.cancel();

        this.recognition.onstart = () => {
            this.isListening = true;
        };

        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            if (onResult) onResult(transcript);
        };

        this.recognition.onerror = (event) => {
            console.error("Voice Error:", event.error);
            this.isListening = false;
            if (onEnd) onEnd();
        };

        this.recognition.onend = () => {
            this.isListening = false;
            if (onEnd) onEnd();
        };

        this.recognition.start();
    }

    /**
     * Reads text aloud using TTS
     * @param {string} text - Text to speak
     * @param {Function} [onEnd] - Optional callback when speech finishes
     */
    speak(text, onEnd) {
        if (!text) {
            if (onEnd) onEnd();
            return;
        }

        // Cancel current queue
        this.synthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        if (this.preferredVoice) {
            utterance.voice = this.preferredVoice;
        }

        // Humanize parameters
        utterance.rate = 1.0;
        utterance.pitch = 1.05;

        // Callback handling
        if (onEnd) {
            utterance.onend = () => {
                onEnd();
            };
            utterance.onerror = (e) => {
                console.error("TTS Error:", e);
                onEnd(); // Ensure flow continues even on error
            };
        }

        this.synthesis.speak(utterance);
    }

    stop() {
        this.synthesis.cancel();
        if (this.isListening) {
            this.recognition.stop();
        }
    }
}

// Export global instance
window.VoiceAssistant = new VoiceAssistant();
