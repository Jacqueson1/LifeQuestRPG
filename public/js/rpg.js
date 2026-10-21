// 1. Web Audio API Synthesizer for Retro RPG Sound Effects
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

const SoundEffects = {
    // Retro Coin / XP Chime
    playXP() {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.type = 'triangle';
        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
        osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.08); // A5

        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.start();
        osc.stop(audioCtx.currentTime + 0.3);
    },

    // Victory Level-Up Fanfare
    playLevelUp() {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        const notes = [261.63, 329.63, 392.00, 523.25]; // C4, E4, G4, C5 (Major chord arpeggio)
        
        notes.forEach((freq, index) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = 'square'; // 8-bit chiptune sound
            osc.frequency.setValueAtTime(freq, audioCtx.currentTime + (index * 0.12));

            gain.gain.setValueAtTime(0.2, audioCtx.currentTime + (index * 0.12));
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + (index * 0.12) + 0.25);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(audioCtx.currentTime + (index * 0.12));
            osc.stop(audioCtx.currentTime + (index * 0.12) + 0.25);
        });
    }
};

// 2. DOM Quest Interactions & Anime.js
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    window.completeQuest = function(taskId, event) {
        const button = event.target;
        button.disabled = true;

        fetch(`/tasks/${taskId}/complete`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 🔊 Play XP sound effect
                SoundEffects.playXP();

                // Floating XP animation
                spawnFloatingXP(event.clientX, event.clientY, data.earned_xp);

                // Animate XP Progress Bar with Anime.js
                const newPercent = Math.min(100, Math.round((data.new_xp / data.xp_needed) * 100));
                anime({
                    targets: '#xpBar',
                    width: `${newPercent}%`,
                    easing: 'easeOutElastic(1, .8)',
                    duration: 1200
                });

                const xpText = document.getElementById('xpText');
                if (xpText) {
                    xpText.innerText = `${data.new_xp} / ${data.xp_needed} XP (${newPercent}%)`;
                }

                // Level-Up Animation + Fanfare
                if (data.leveled_up) {
                    const badge = document.getElementById('levelBadge');
                    if (badge) badge.innerText = `LVL ${data.new_level}`;

                    // 🔊 Play Level-Up fanfare
                    setTimeout(() => {
                        SoundEffects.playLevelUp();
                    }, 300);

                    anime({
                        targets: '#hudCard',
                        boxShadow: [
                            '0 0 0px rgba(251, 191, 36, 0)',
                            '0 0 40px rgba(251, 191, 36, 0.9)',
                            '0 10px 25px -5px rgba(0, 0, 0, 0.5)'
                        ],
                        scale: [1, 1.03, 1],
                        duration: 1500,
                        easing: 'easeInOutQuad'
                    });
                }

                // Mark Task Card as Completed
                const card = document.getElementById(`task-${taskId}`);
                if (card) {
                    anime({
                        targets: card,
                        opacity: 0.5,
                        duration: 600,
                        easing: 'easeOutQuad',
                        complete: () => {
                            card.classList.add('completed');
                            button.remove();
                        }
                    });
                }
            }
        })
        .catch(err => {
            console.error('Quest completion failed:', err);
            button.disabled = false;
        });
    };

    function spawnFloatingXP(x, y, xp) {
        const el = document.createElement('div');
        el.className = 'floating-xp';
        el.innerText = `+${xp} XP!`;
        el.style.left = `${x}px`;
        el.style.top = `${y}px`;
        document.body.appendChild(el);

        anime({
            targets: el,
            translateY: -70,
            opacity: [1, 0],
            duration: 1200,
            easing: 'easeOutCubic',
            complete: () => el.remove()
        });
    }
});
