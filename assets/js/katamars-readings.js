/* Katamars Readings JS */
document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.katamars-container');
    const contentArea = document.querySelector('.katamars-readings');
    const loader = document.createElement('div');
    loader.className = 'katamars-loader';
    if (contentArea) contentArea.parentNode.insertBefore(loader, contentArea);

    // AJAX Navigation for Days
    // AJAX Navigation for Days - DISABLED for reliability
    /*
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.katamars-nav-link')) {
            e.preventDefault();
            // ...
        }
    });
    */

    // Audio / TTS Logic
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#katamars-audio-btn')) {
            const btn = e.target.closest('#katamars-audio-btn');
            const text = document.querySelector('.katamars-readings').innerText;
            const isArabic = document.documentElement.lang === 'ar';

            if (window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                btn.classList.remove('active');
                btn.innerHTML = '<span class="icon">🔊</span> ' + (isArabic ? 'استماع' : 'Listen');
            } else {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = isArabic ? 'ar-EG' : 'en-US';
                window.speechSynthesis.speak(utterance);
                btn.classList.add('active');
                btn.innerHTML = '<span class="icon">⏹</span> ' + (isArabic ? 'إيقاف' : 'Stop');

                utterance.onend = function () {
                    btn.classList.remove('active');
                    btn.innerHTML = '<span class="icon">🔊</span> ' + (isArabic ? 'استماع' : 'Listen');
                };
            }
        }
    });

    // Layout Toggle Logic
    // Layout Toggle Logic - REMOVED per user request
    /*
    const readingsContainer = document.querySelector('.katamars-readings');
    if (readingsContainer && localStorage.getItem('katamars-layout') === 'inline') {
        readingsContainer.classList.add('layout-inline');
    }

    document.body.addEventListener('click', function (e) {
        // ...
    });
    */

    // Floating Navigation Logic
    function initFloatingNav() {
        const sections = document.querySelectorAll('.katamars-reading-section, .katamars-synaxarium');
        const navLinks = document.querySelectorAll('.katamars-nav-item');

        if (!sections.length || !navLinks.length) return;

        // Smooth Scroll
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Scroll Spy
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        });
    }

    // Copy All Readings Button
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#katamars-copy-all-btn')) {
            copyAllReadings();
        }
    });

    function copyAllReadings() {
        const isArabic = document.documentElement.lang === 'ar';
        let formattedText = '';

        // Get Title
        const header = document.querySelector('.katamars-header h2');
        const occasion = document.querySelector('.katamars-occasion h3');

        if (occasion) {
            formattedText += '📖 ' + occasion.textContent.trim() + ' 📖\n\n';
        } else if (header) {
            formattedText += '📖 قراءات ' + header.textContent.trim() + ' 📖\n\n';
        }

        // Collect readings by service
        formattedText = collectServiceReadings(formattedText, '🌙 صلاة العشية', 'العشية', 'Vespers');
        formattedText = collectServiceReadings(formattedText, '🌅 صلاة باكر', 'باكر', 'Matins');
        formattedText = collectServiceReadings(formattedText, '✝️ القداس الإلهي', 'القداس', 'Liturgy');

        // Add Synaxarium
        const synaxarium = document.querySelector('#section-synaxarium');
        if (synaxarium) {
            const synaxTitle = synaxarium.querySelector('h3');
            const synaxContent = synaxarium.querySelector('.katamars-synax-content');
            if (synaxTitle && synaxContent) {
                formattedText += '📜 ' + synaxTitle.textContent.trim() + '\n';
                formattedText += '━━━━━━━━━━━━━━━━━━━━\n';
                formattedText += cleanText(synaxContent.textContent) + '\n\n';
            }
        }

        // Add footer
        formattedText += '━━━━━━━━━━━━━━━━━━━━\n';
        formattedText += '🙏 صلواتكم 🙏\n';

        // Copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(formattedText).then(() => {
                showNotification(isArabic ? 'تم نسخ جميع القراءات! 📋' : 'All readings copied! 📋');
            }).catch(() => fallbackCopy(formattedText));
        } else {
            fallbackCopy(formattedText);
        }
    }

    function collectServiceReadings(text, title, arKeyword, enKeyword) {
        const headers = document.querySelectorAll('.katamars-service-header');
        let foundService = false;

        headers.forEach(header => {
            if (header.textContent.includes(arKeyword) || header.textContent.includes(enKeyword)) {
                if (!foundService) {
                    text += title + '\n';
                    text += '━━━━━━━━━━━━━━━━━━━━\n';
                    foundService = true;
                }

                let nextElement = header.nextElementSibling;
                while (nextElement && !nextElement.classList.contains('katamars-service-header')) {
                    if (nextElement.classList.contains('katamars-reading-section')) {
                        const readingTitle = nextElement.querySelector('h4');
                        const readings = nextElement.querySelectorAll('.katamars-reading');

                        readings.forEach(reading => {
                            const reference = reading.querySelector('.katamars-reference');
                            const readingText = reading.querySelector('.katamars-text');

                            if (reference && readingText) {
                                text += '📜 ' + (readingTitle ? readingTitle.textContent.trim() + ': ' : '');
                                text += reference.textContent.trim() + '\n';
                                text += cleanText(readingText.textContent) + '\n\n';
                            }
                        });
                    }
                    nextElement = nextElement.nextElementSibling;
                }
            }
        });

        if (foundService) text += '\n';
        return text;
    }

    function cleanText(text) {
        return text.trim().replace(/\s+/g, ' ').replace(/\n\s*\n/g, '\n');
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showNotification('تم النسخ! 📋');
    }

    function showNotification(message) {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.style.cssText = 'position:fixed;top:20px;right:20px;background:linear-gradient(135deg,#4CAF50,#45a049);color:white;padding:15px 25px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.2);z-index:1000000;font-weight:bold;animation:slideIn 0.3s ease';
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Initial Call
    initFloatingNav();
});
