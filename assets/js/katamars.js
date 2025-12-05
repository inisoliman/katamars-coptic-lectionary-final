document.addEventListener('DOMContentLoaded', function () {
    // Initialize UI Elements
    const container = document.querySelector('.katamars-container');
    const contentArea = document.querySelector('.katamars-readings');
    const loader = document.createElement('div');
    loader.className = 'katamars-loader';
    if (contentArea) contentArea.parentNode.insertBefore(loader, contentArea);

    // Dark Mode Toggle
    const themeToggle = document.createElement('button');
    themeToggle.className = 'theme-toggle';
    themeToggle.innerHTML = '🌙';
    document.body.appendChild(themeToggle);

    // Check Local Storage for Theme
    if (localStorage.getItem('katamars-theme') === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        themeToggle.innerHTML = '☀️';
    }

    themeToggle.addEventListener('click', () => {
        if (document.body.getAttribute('data-theme') === 'dark') {
            document.body.removeAttribute('data-theme');
            localStorage.setItem('katamars-theme', 'light');
            themeToggle.innerHTML = '🌙';
        } else {
            document.body.setAttribute('data-theme', 'dark');
            localStorage.setItem('katamars-theme', 'dark');
            themeToggle.innerHTML = '☀️';
        }
    });

    // AJAX Navigation
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.katamars-nav-link')) {
            e.preventDefault();
            const link = e.target.closest('.katamars-nav-link');
            const url = link.href;

            // Show Loader
            if (contentArea) contentArea.style.opacity = '0.5';
            loader.style.display = 'block';

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('.katamars-container').innerHTML;

                    if (container && newContent) {
                        container.innerHTML = newContent;
                        // Update URL without refresh
                        window.history.pushState({}, '', url);
                    }
                })
                .catch(err => console.error('Error loading readings:', err))
                .finally(() => {
                    if (contentArea) contentArea.style.opacity = '1';
                    loader.style.display = 'none';
                });
        }
    });

    // Audio / TTS Logic (Daily Readings)
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#katamars-audio-btn')) {
            const btn = e.target.closest('#katamars-audio-btn');
            const text = document.querySelector('.katamars-readings').innerText;

            if (window.speechSynthesis.speaking) {
                window.speechSynthesis.cancel();
                btn.classList.remove('active');
                btn.innerHTML = '<span class="icon">🔊</span> ' + (document.documentElement.lang === 'ar' ? 'استماع' : 'Listen');
            } else {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = document.documentElement.lang === 'ar' ? 'ar-EG' : 'en-US';
                window.speechSynthesis.speak(utterance);
                btn.classList.add('active');
                btn.innerHTML = '<span class="icon">⏹</span> ' + (document.documentElement.lang === 'ar' ? 'إيقاف' : 'Stop');

                utterance.onend = function () {
                    btn.classList.remove('active');
                    btn.innerHTML = '<span class="icon">🔊</span> ' + (document.documentElement.lang === 'ar' ? 'استماع' : 'Listen');
                };
            }
        }
    });

    // Layout Toggle Logic
    const readingsContainer = document.querySelector('.katamars-readings');
    if (readingsContainer && localStorage.getItem('katamars-layout') === 'list') {
        readingsContainer.classList.add('layout-list');
    }

    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#katamars-layout-btn')) {
            const container = document.querySelector('.katamars-readings');
            if (container) {
                container.classList.toggle('layout-list');
                const isList = container.classList.contains('layout-list');
                localStorage.setItem('katamars-layout', isList ? 'list' : 'inline');
            }
        }
    });

    // Bible Browser: Font Size Control
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#font-increase')) {
            adjustFontSize(2);
        } else if (e.target.closest('#font-decrease')) {
            adjustFontSize(-2);
        }
    });

    function adjustFontSize(delta) {
        const content = document.getElementById('bible-text-content');
        if (!content) return;

        const style = window.getComputedStyle(content, null).getPropertyValue('font-size');
        const currentSize = parseFloat(style);
        const newSize = currentSize + delta;

        // Limits (14px to 40px)
        if (newSize >= 14 && newSize <= 40) {
            content.style.fontSize = newSize + 'px';
            localStorage.setItem('katamars-bible-font-size', newSize);
        }
    }

    // Restore Font Size
    const savedFontSize = localStorage.getItem('katamars-bible-font-size');
    if (savedFontSize) {
        const content = document.getElementById('bible-text-content');
        if (content) {
            content.style.fontSize = savedFontSize + 'px';
        }
    }

    // Bible Browser: Audio TTS
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('#bible-audio-btn');
        if (btn) {
            const text = btn.getAttribute('data-text');
            if (text) {
                if (window.speechSynthesis.speaking) {
                    window.speechSynthesis.cancel();
                    btn.classList.remove('playing');
                    btn.innerHTML = '<span class="icon">🔊</span> استماع';
                } else {
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'ar-EG'; // Default to Arabic
                    utterance.rate = 0.9;

                    utterance.onend = function () {
                        btn.classList.remove('playing');
                        btn.innerHTML = '<span class="icon">🔊</span> استماع';
                    };

                    window.speechSynthesis.speak(utterance);
                    btn.classList.add('playing');
                    btn.innerHTML = '<span class="icon">⏹</span> إيقاف';
                }
            }
        }
    });
});
