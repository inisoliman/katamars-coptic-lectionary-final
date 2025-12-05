/* Katamars Core JS - Without Theme Toggle */
document.addEventListener('DOMContentLoaded', function () {
    // Language Toggle Logic (Global)
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#katamars-lang-btn')) {
            const currentUrl = new URL(window.location.href);
            const currentLang = currentUrl.searchParams.get('lang') || 'ar';
            const newLang = currentLang === 'ar' ? 'en' : 'ar';

            currentUrl.searchParams.set('lang', newLang);
            window.location.href = currentUrl.toString();
        }
    });
});
