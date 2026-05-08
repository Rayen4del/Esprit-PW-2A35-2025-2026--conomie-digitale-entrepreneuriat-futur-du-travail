// emotion.js — detects emotion once on comment submit
const EMOTION_SERVER = 'http://localhost:3001/detect-emotion';

const emotionEmojis = {
    joy: '😄', happiness: '😄', happy: '😄',
    sadness: '😢', sad: '😢',
    anger: '😠', angry: '😠',
    fear: '😨',
    surprise: '😲',
    disgust: '🤢',
    neutral: '😐',
    love: '❤️',
    excitement: '🤩'
};

async function detectEmotion(text) {
    try {
        console.debug('Emotion detection request:', EMOTION_SERVER, text);
        const res = await fetch(EMOTION_SERVER, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text })
        });
        const data = await res.json();
        if (!res.ok) {
            console.warn('Emotion detection failed response:', res.status, data);
            return 'neutral';
        }
        const emotion = (data.primaryEmotion || 'neutral').toString().toLowerCase();
        console.debug('Emotion detection result:', emotion, data);
        return emotion;
    } catch (e) {
        console.warn('Emotion detection failed, falling back to neutral:', e);
        return 'neutral';
    }
}

function emotionToEmoji(emotion) {
    return emotionEmojis[emotion] || '🎭';
}