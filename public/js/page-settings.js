(function () {
    const form = document.getElementById('page-settings-form');
    if (!form) return;

    if (window.CKEDITOR && document.getElementById('page-content')) {
        CKEDITOR.replace('page-content', {
            height: 320,
            removePlugins: 'exportpdf',
            versionCheck: false,
        });
    }

    form.addEventListener('submit', function () {
        if (window.CKEDITOR && CKEDITOR.instances['page-content']) {
            CKEDITOR.instances['page-content'].updateElement();
        }
    });
})();
