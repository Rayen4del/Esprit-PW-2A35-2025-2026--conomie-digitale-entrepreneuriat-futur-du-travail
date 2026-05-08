// speech-to-text.js
let ws = null;
let isListening = false;
let audioStream = null;
let audioContext = null;
let processor = null;

const speakBtn = document.getElementById('speakBtn');
const speakBtnText = document.getElementById('speakBtnText');
const statusText = document.getElementById('speechStatus');

// Initialize the feature
function initSpeechToText() {
    if (!speakBtn) return;
    speakBtn.addEventListener('click', toggleListening);
}

function toggleListening() {
    if (!isListening) {
        startListening();
    } else {
        stopListening();
    }
}

function startListening() {
    // Connect to Node.js WebSocket server
    ws = new WebSocket('ws://localhost:3000');

    ws.onopen = () => {
        console.log('%c✅ Connected to Speech-to-Text Server', 'color: green; font-weight: bold');
        isListening = true;
        
        speakBtn.classList.remove('btn-outline-primary');
        speakBtn.classList.add('btn-danger');
        speakBtnText.innerHTML = '🎤 Listening... Click to Stop';
        statusText.textContent = '🎙️ Requesting microphone access...';
        
        // Request microphone access
        requestMicrophone();
    };

    ws.onerror = (error) => {
        console.error('WebSocket Error:', error);
        statusText.textContent = '❌ Connection error. Is Node.js server running?';
        stopListening();
    };

    ws.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);
            if (data.text && data.text.trim() !== '') {
                // Append text to Quill Editor
                const quill = quillCreateEditor;
                if (quill) {
                    quill.focus();
                    const range = quill.getSelection() || { index: quill.getLength() };
                    quill.insertText(range.index, data.text + ' ');
                    quill.setSelection(range.index + data.text.length + 1);
                }
            }
        } catch (e) {
            console.error('Error parsing message:', e);
        }
    };

    ws.onclose = () => {
        stopListening();
    };
}

function requestMicrophone() {
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            audioStream = stream;
            statusText.textContent = '🎙️ Speak now... (Tunisian Arabic supported)';
            setupAudioProcessing(stream);
        })
        .catch(error => {
            console.error('Microphone access error:', error);
            if (error.name === 'NotAllowedError') {
                statusText.textContent = '❌ Microphone permission denied. Please allow mic access.';
            } else if (error.name === 'NotFoundError') {
                statusText.textContent = '❌ No microphone found. Check your device.';
            } else {
                statusText.textContent = '❌ Error accessing microphone: ' + error.message;
            }
            stopListening();
        });
}

function setupAudioProcessing(stream) {
    try {
        audioContext = new (window.AudioContext || window.webkitAudioContext)({ sampleRate: 16000 });
        const source = audioContext.createMediaStreamSource(stream);
        processor = audioContext.createScriptProcessor(4096, 1, 1);

        source.connect(processor);
        processor.connect(audioContext.destination);

        processor.onaudioprocess = (e) => {
            const input = e.inputBuffer.getChannelData(0);
            const buffer = new Int16Array(input.length);

            // Convert float samples to 16-bit integer samples
            for (let i = 0; i < input.length; i++) {
                buffer[i] = Math.max(-1, Math.min(1, input[i])) * 0x7fff;
            }

            // Send audio data to server if connected
            if (ws && ws.readyState === 1) {
                ws.send(buffer.buffer);
            }
        };
    } catch (error) {
        console.error('Audio processing error:', error);
        statusText.textContent = '❌ Error setting up audio: ' + error.message;
        stopListening();
    }
}

function stopListening() {
    // Stop WebSocket
    if (ws) {
        ws.close();
        ws = null;
    }

    // Stop audio stream
    if (audioStream) {
        audioStream.getTracks().forEach(track => track.stop());
        audioStream = null;
    }

    // Close audio context
    if (audioContext) {
        audioContext.close();
        audioContext = null;
    }

    // Disconnect processor
    if (processor) {
        processor.disconnect();
        processor = null;
    }

    isListening = false;

    speakBtn.classList.remove('btn-danger');
    speakBtn.classList.add('btn-outline-primary');
    speakBtnText.innerHTML = '<i class="bx bx-mic"></i> Start Speaking';
    statusText.textContent = '';
}

// Auto initialize when script loads
document.addEventListener('DOMContentLoaded', initSpeechToText);