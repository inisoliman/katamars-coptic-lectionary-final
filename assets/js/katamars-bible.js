/* Katamars Bible JS - Without Dark Mode */
document.addEventListener('DOMContentLoaded', function () {

    // Font Size Control
    let currentFontSize = parseInt(localStorage.getItem('katamars-bible-font-size')) || 22;

    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#font-increase')) {
            adjustFontSize(2);
        } else if (e.target.closest('#font-decrease')) {
            adjustFontSize(-2);
        }
    });

    function adjustFontSize(delta) {
        const content = document.querySelector('.katamars-chapter-content');
        if (!content) return;

        currentFontSize += delta;

        // Limits (14px to 32px)
        if (currentFontSize >= 14 && currentFontSize <= 32) {
            content.style.fontSize = currentFontSize + 'px';
            localStorage.setItem('katamars-bible-font-size', currentFontSize);
            updateFontSizeDisplay();
        } else {
            currentFontSize -= delta;
        }
    }

    function updateFontSizeDisplay() {
        const display = document.querySelector('.font-size-display');
        if (display) {
            display.textContent = currentFontSize + 'px';
        }
    }

    // Restore Font Size
    const content = document.querySelector('.katamars-chapter-content');
    if (content && currentFontSize) {
        content.style.fontSize = currentFontSize + 'px';
        updateFontSizeDisplay();
    }

    // Copy Verse Functionality
    document.body.addEventListener('click', function (e) {
        const verse = e.target.closest('.katamars-verse');
        if (verse && e.ctrlKey) {
            const verseNum = verse.querySelector('.verse-number')?.textContent || '';
            const verseText = verse.textContent.replace(verseNum, '').trim();
            const bookName = document.querySelector('.katamars-bible-nav')?.textContent || '';
            const fullText = `${bookName} - آية ${verseNum}: ${verseText}`;

            copyToClipboard(fullText);
            showNotification('تم نسخ الآية! 📋');
        }
    });

    // Copy to clipboard helper
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }

    // Show notification
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'copy-notification';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Share Verse
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#share-btn')) {
            const bookName = document.querySelector('.katamars-bible-nav')?.textContent || '';
            const url = window.location.href;
            const text = `اقرأ ${bookName} على القطمارس`;

            if (navigator.share) {
                navigator.share({
                    title: bookName,
                    text: text,
                    url: url
                }).catch(() => { });
            } else {
                copyToClipboard(url);
                showNotification('تم نسخ الرابط! 🔗');
            }
        }
    });

    // Bible Audio TTS
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('#bible-audio-btn');
        if (btn) {
            const content = document.querySelector('.katamars-chapter-content');
            const text = content?.textContent || '';
            const bibleReader = document.querySelector('.katamars-bible-reader');
            const isArabic = bibleReader?.getAttribute('dir') === 'rtl' || document.documentElement.lang === 'ar';

            if (text) {
                if (window.speechSynthesis.speaking) {
                    window.speechSynthesis.cancel();
                    btn.classList.remove('playing');
                    btn.innerHTML = '<span class="icon">🔊</span> ' + (isArabic ? 'استماع' : 'Listen');
                } else {
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = isArabic ? 'ar-SA' : 'en-US';
                    utterance.rate = 0.85;
                    utterance.pitch = 1.0;

                    utterance.onend = function () {
                        btn.classList.remove('playing');
                        btn.innerHTML = '<span class="icon">🔊</span> ' + (isArabic ? 'استماع' : 'Listen');
                    };

                    window.speechSynthesis.speak(utterance);
                    btn.classList.add('playing');
                    btn.innerHTML = '<span class="icon">⏹</span> ' + (isArabic ? 'إيقاف' : 'Stop');
                }
            }
        }
    });

    // Add visual feedback for Ctrl+Click hint
    const verses = document.querySelectorAll('.katamars-verse');
    verses.forEach(verse => {
        verse.setAttribute('title', 'اضغط Ctrl+Click لنسخ الآية');
    });
});
