/* Katamars Widget JavaScript */
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('katamars-verse-modal');
    if (!modal) return; // Widget not on page

    const modalTitle = document.getElementById('modal-title');
    const modalVerses = document.getElementById('modal-verses');
    const modalCopyBtn = document.getElementById('modal-copy-btn');
    const modalChapterLink = document.getElementById('modal-chapter-link');
    const modalClose = modal.querySelector('.modal-close');
    const modalOverlay = modal.querySelector('.modal-overlay');

    let currentReference = '';
    let currentLabel = '';

    // Click on reading link
    document.body.addEventListener('click', function (e) {
        const link = e.target.closest('.reading-link');
        if (link) {
            e.preventDefault();
            currentReference = link.getAttribute('data-reference');
            currentLabel = link.getAttribute('data-label');
            openModal(currentReference, currentLabel);
        }
    });

    // Hover preview (optional - shows tooltip)
    document.body.addEventListener('mouseenter', function (e) {
        const link = e.target.closest('.reading-link');
        if (link) {
            const reference = link.getAttribute('data-reference');
            // Could add quick preview here
            // For now, just show reference in tooltip
            link.setAttribute('title', 'انقر لعرض الآيات');
        }
    }, true);

    // Open modal and load verses
    function openModal(reference, label) {
        modal.style.display = 'flex';
        modalTitle.textContent = label + ': ' + reference;
        modalVerses.innerHTML = '<div class="loading">جاري التحميل...</div>';

        // Parse reference to get book and chapter for link
        const chapterLink = getChapterLink(reference);
        if (chapterLink) {
            modalChapterLink.href = chapterLink;
            modalChapterLink.style.display = 'inline-block';
        } else {
            modalChapterLink.style.display = 'none';
        }

        // Load verses via AJAX
        loadVerses(reference);
    }

    // Close modal
    function closeModal() {
        modal.style.display = 'none';
    }

    modalClose.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', closeModal);

    // ESC key to close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // Load verses via AJAX
    function loadVerses(reference) {
        // Use WordPress AJAX
        const data = new FormData();
        data.append('action', 'katamars_get_verses');
        data.append('reference', reference);
        data.append('nonce', katamarWidget.nonce);

        fetch(katamarWidget.ajaxUrl, {
            method: 'POST',
            body: data
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    modalVerses.innerHTML = result.data.html;
                } else {
                    modalVerses.innerHTML = '<p class="error">حدث خطأ في تحميل الآيات</p>';
                }
            })
            .catch(error => {
                console.error('Error loading verses:', error);
                modalVerses.innerHTML = '<p class="error">حدث خطأ في الاتصال</p>';
            });
    }

    // Copy verses to clipboard
    modalCopyBtn.addEventListener('click', function () {
        const versesText = modalVerses.textContent;
        const fullText = modalTitle.textContent + '\n\n' + versesText;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(fullText).then(() => {
                showCopyNotification();
            });
        } else {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = fullText;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showCopyNotification();
        }
    });

    // Show copy notification
    function showCopyNotification() {
        const notification = document.createElement('div');
        notification.className = 'copy-notification';
        notification.textContent = 'تم النسخ! 📋';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            z-index: 1000000;
            animation: slideInRight 0.3s ease;
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Get chapter link from reference
    function getChapterLink(reference) {
        // Parse reference like "Psalms 86:2-4" or "Matthew 11:25-30"
        const parts = reference.split(' ');
        if (parts.length < 2) return null;

        const chapterVerse = parts[parts.length - 1];
        const bookParts = parts.slice(0, -1);
        const bookName = bookParts.join(' ');

        const chapterMatch = chapterVerse.match(/^(\d+)/);
        if (!chapterMatch) return null;

        const chapter = chapterMatch[1];

        // Convert book name to slug
        const bookSlug = convertBookNameToSlug(bookName);
        if (!bookSlug) return null;

        // Build URL
        return katamarWidget.homeUrl + '/katamars_bible/' + bookSlug + '/' + chapter;
    }

    // Convert book name to slug
    function convertBookNameToSlug(bookName) {
        const bookMap = {
            'Genesis': 'genesis',
            'Exodus': 'exodus',
            'Leviticus': 'leviticus',
            'Numbers': 'numbers',
            'Deuteronomy': 'deuteronomy',
            'Joshua': 'joshua',
            'Judges': 'judges',
            'Ruth': 'ruth',
            '1 Samuel': '1-samuel',
            '2 Samuel': '2-samuel',
            '1 Kings': '1-kings',
            '2 Kings': '2-kings',
            '1 Chronicles': '1-chronicles',
            '2 Chronicles': '2-chronicles',
            'Ezra': 'ezra',
            'Nehemiah': 'nehemiah',
            'Esther': 'esther',
            'Job': 'job',
            'Psalms': 'psalms',
            'Proverbs': 'proverbs',
            'Ecclesiastes': 'ecclesiastes',
            'Song of Solomon': 'song-of-solomon',
            'Isaiah': 'isaiah',
            'Jeremiah': 'jeremiah',
            'Lamentations': 'lamentations',
            'Ezekiel': 'ezekiel',
            'Daniel': 'daniel',
            'Hosea': 'hosea',
            'Joel': 'joel',
            'Amos': 'amos',
            'Obadiah': 'obadiah',
            'Jonah': 'jonah',
            'Micah': 'micah',
            'Nahum': 'nahum',
            'Habakkuk': 'habakkuk',
            'Zephaniah': 'zephaniah',
            'Haggai': 'haggai',
            'Zechariah': 'zechariah',
            'Malachi': 'malachi',
            'Matthew': 'matthew',
            'Mark': 'mark',
            'Luke': 'luke',
            'John': 'john',
            'Acts': 'acts',
            'Romans': 'romans',
            '1 Corinthians': '1-corinthians',
            '2 Corinthians': '2-corinthians',
            'Galatians': 'galatians',
            'Ephesians': 'ephesians',
            'Philippians': 'philippians',
            'Colossians': 'colossians',
            '1 Thessalonians': '1-thessalonians',
            '2 Thessalonians': '2-thessalonians',
            '1 Timothy': '1-timothy',
            '2 Timothy': '2-timothy',
            'Titus': 'titus',
            'Philemon': 'philemon',
            'Hebrews': 'hebrews',
            'James': 'james',
            '1 Peter': '1-peter',
            '2 Peter': '2-peter',
            '1 John': '1-john',
            '2 John': '2-john',
            '3 John': '3-john',
            'Jude': 'jude',
            'Revelation': 'revelation'
        };

        return bookMap[bookName] || null;
    }
});
