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
    excitement: '🤩',
    approval: '👍',
    admiration: '🤩',
    gratitude: '🙏',
    optimism: '😊',
    pride: '😎',
    caring: '🤗',
    desire: '😍',
    amusement: '😂',
    relief: '😌',
    curiosity: '🤔',
    surprise: '😲',
    disappointment: '😞',
    annoyance: '😒',
    embarrassment: '😳',
    contempt: '😤',
    boredom: '😴',
    realization: '💡'
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
        console.log('📥 Full Raw API Response:', data);   // ← This will help us debug

        if (!res.ok) {
            console.warn('Emotion detection failed:', res.status, data);
            return 'neutral';
        }

        let emotion = 'neutral';
        let maxScore = -Infinity;

        // 1. Best source: segments
        if (data.segments && Array.isArray(data.segments)) {
            for (const segment of data.segments) {
                if (segment.emotions && typeof segment.emotions === 'object') {
                    for (const [emo, score] of Object.entries(segment.emotions)) {
                        const numericScore = Number(score) || 0;
                        if (numericScore > maxScore) {
                            maxScore = numericScore;
                            emotion = emo.toString().toLowerCase();
                        }
                    }
                }
            }
        }

        // 2. Top-level emotions object
        if (maxScore < 0.1 && data.emotions && typeof data.emotions === 'object') {
            for (const [emo, score] of Object.entries(data.emotions)) {
                const numericScore = Number(score) || 0;
                if (numericScore > maxScore) {
                    maxScore = numericScore;
                    emotion = emo.toString().toLowerCase();
                }
            }
        }

        // 3. Final fallbacks
        if (maxScore < 0.1) {
            emotion = (data.primaryEmotion || 
                      data.dominant_emotion || 
                      data.emotion || 
                      'neutral').toString().toLowerCase();
        }

        // Safety cleanup
        if (!emotion || emotion === '0' || emotion === 'null') {
            emotion = 'neutral';
        }

        console.debug('✅ Final detected emotion:', emotion, '| Max Score:', maxScore);
        return emotion;

    } catch (e) {
        console.error('Emotion detection crashed:', e);
        return 'neutral';
    }
}
function emotionToEmoji(emotion) {
    return emotionEmojis[emotion] || '🎭';
}