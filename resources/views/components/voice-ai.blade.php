{{-- resources/views/components/voice-ai.blade.php --}}

<style>
    .voice-ai-launcher {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1050;
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        box-shadow: 0 8px 25px rgba(37, 99, 235, .35);
        cursor: pointer;
        transition: transform .2s ease;
    }

    .voice-ai-launcher:hover {
        transform: scale(1.06);
    }

    .voice-ai-panel {
        position: fixed;
        right: 24px;
        bottom: 94px;
        z-index: 1051;
        width: min(390px, calc(100vw - 32px));
        display: none;
        overflow: hidden;
        border: 1px solid rgba(128, 128, 128, .25);
        border-radius: 18px;
        background: var(--bs-body-bg, #fff);
        color: var(--bs-body-color, #212529);
        box-shadow: 0 15px 50px rgba(0, 0, 0, .2);
    }

    .voice-ai-panel.is-visible {
        display: block;
    }

    .voice-ai-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 18px;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
    }

    .voice-ai-body {
        padding: 18px;
    }

    .voice-ai-response {
        min-height: 80px;
        margin-top: 14px;
        padding: 12px;
        border-radius: 12px;
        background: var(--bs-tertiary-bg, #f8f9fa);
        white-space: pre-wrap;
        font-size: 14px;
    }

    .voice-ai-mic {
        width: 52px;
        height: 52px;
        border: none;
        border-radius: 50%;
        background: #dc3545;
        color: #fff;
        cursor: pointer;
    }

    .voice-ai-mic.listening {
        animation: voiceAiPulse 1.2s infinite;
    }

    .voice-ai-status {
        margin-top: 8px;
        font-size: 12px;
        color: var(--bs-secondary-color, #6c757d);
    }

    @keyframes voiceAiPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, .5);
        }

        70% {
            box-shadow: 0 0 0 14px rgba(220, 53, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    @media (max-width: 576px) {
        .voice-ai-launcher {
            right: 16px;
            bottom: 16px;
        }

        .voice-ai-panel {
            right: 16px;
            bottom: 86px;
        }
    }
</style>

<button
    type="button"
    class="voice-ai-launcher"
    id="voiceAiLauncher"
    aria-label="Open Admin Voice AI"
    title="Admin Voice AI">
    <i class="fas fa-microphone-alt fs-5"></i>
</button>

<section
    class="voice-ai-panel"
    id="voiceAiPanel"
    aria-label="Admin Voice AI panel">

    <div class="voice-ai-header">
        <div>
            <strong>
                <i class="fas fa-robot me-2"></i>
                Admin Voice AI
            </strong>
            <div class="small opacity-75">
                Ask EDUNEXUS a question
            </div>
        </div>

        <button
            type="button"
            class="btn btn-sm btn-light"
            id="voiceAiClose"
            aria-label="Close Voice AI">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="voice-ai-body">
        <label for="voiceAiCommand" class="form-label">
            Type or speak your command
        </label>

        <textarea
            id="voiceAiCommand"
            class="form-control"
            rows="3"
            placeholder="Example: Give me today's school summary."></textarea>

        <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
            <button
                type="button"
                class="voice-ai-mic"
                id="voiceAiMic"
                title="Speak command"
                aria-label="Speak command">
                <i class="fas fa-microphone"></i>
            </button>

            <button
                type="button"
                class="btn btn-primary"
                id="voiceAiSend">
                <i class="fas fa-paper-plane me-1"></i>
                Ask AI
            </button>
        </div>

        <div class="voice-ai-status" id="voiceAiStatus">
            Ready. You can type a command or use the microphone.
        </div>

        <div class="voice-ai-response" id="voiceAiResponse">
            Your AI response will appear here.
        </div>
    </div>
</section>

<script>
(() => {
    'use strict';

    const launcher = document.getElementById('voiceAiLauncher');
    const panel = document.getElementById('voiceAiPanel');
    const closeButton = document.getElementById('voiceAiClose');
    const microphone = document.getElementById('voiceAiMic');
    const commandInput = document.getElementById('voiceAiCommand');
    const sendButton = document.getElementById('voiceAiSend');
    const statusText = document.getElementById('voiceAiStatus');
    const responseBox = document.getElementById('voiceAiResponse');

    if (
        !launcher ||
        !panel ||
        !closeButton ||
        !microphone ||
        !commandInput ||
        !sendButton ||
        !statusText ||
        !responseBox
    ) {
        return;
    }

    let recognition = null;
    let isListening = false;

    launcher.addEventListener('click', () => {
        panel.classList.toggle('is-visible');

        if (panel.classList.contains('is-visible')) {
            commandInput.focus();
        }
    });

    closeButton.addEventListener('click', () => {
        panel.classList.remove('is-visible');
    });

    function setStatus(message) {
        statusText.textContent = message;
    }

    function speakResponse(message) {
        if (!('speechSynthesis' in window)) {
            return;
        }

        window.speechSynthesis.cancel();

        const speech = new SpeechSynthesisUtterance(message);
        speech.lang = 'en-GH';
        speech.rate = 1;
        speech.pitch = 1;

        window.speechSynthesis.speak(speech);
    }

    function setupSpeechRecognition() {
        const SpeechRecognition =
            window.SpeechRecognition ||
            window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            setStatus(
                'Voice recognition is not supported in this browser. You can type your command.'
            );

            microphone.disabled = true;
            microphone.style.opacity = '.5';
            return;
        }

        recognition = new SpeechRecognition();
        recognition.lang = 'en-GH';
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onstart = () => {
            isListening = true;
            microphone.classList.add('listening');
            microphone.innerHTML = '<i class="fas fa-stop"></i>';
            setStatus('Listening... speak your command.');
        };

        recognition.onresult = event => {
            const transcript = event.results[0][0].transcript;

            commandInput.value = transcript;
            setStatus('Command captured. Click Ask AI to continue.');
        };

        recognition.onerror = event => {
            setStatus('Microphone error: ' + event.error);
        };

        recognition.onend = () => {
            isListening = false;
            microphone.classList.remove('listening');
            microphone.innerHTML = '<i class="fas fa-microphone"></i>';
        };
    }

    microphone.addEventListener('click', () => {
        if (!recognition) {
            setStatus('Speech recognition is not available in this browser.');
            return;
        }

        if (isListening) {
            recognition.stop();
            return;
        }

        recognition.start();
    });

    async function submitCommand() {
        const command = commandInput.value.trim();

        if (!command) {
            setStatus('Please enter or speak a command first.');
            commandInput.focus();
            return;
        }

        sendButton.disabled = true;
        microphone.disabled = true;
        responseBox.textContent = 'Thinking...';
        setStatus('Sending your command to EDUNEXUS AI...');

        try {
            const response = await fetch('{{ url('/ai/voice/command') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    command: command
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || 'The AI request failed.'
                );
            }

            const message =
                data.message ||
                data.response ||
                'The AI returned no response.';

            responseBox.textContent = message;
            setStatus('Response received.');

            speakResponse(message);
        } catch (error) {
            responseBox.textContent = error.message;
            setStatus('Unable to process the command.');
        } finally {
            sendButton.disabled = false;
            microphone.disabled = false;
        }
    }

    sendButton.addEventListener('click', submitCommand);

    commandInput.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            submitCommand();
        }
    });

    setupSpeechRecognition();
})();
</script>