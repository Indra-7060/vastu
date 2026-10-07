/**
 * Banner add/edit form: per-section guidance, media card visibility and upload previews.
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('banner-form');
        if (!form) return;

        const section = document.getElementById('banner-section');
        const hint = document.getElementById('section-hint');
        const fields = document.getElementById('section-fields');
        const mediaCard = document.getElementById('banner-media-card');
        const mediaNote = document.getElementById('banner-media-note');
        const input = document.getElementById('banner-images');
        const preview = document.getElementById('banner-image-preview');
        const meta = document.getElementById('banner-image-meta');

        function currentOption() {
            return section ? section.options[section.selectedIndex] : null;
        }

        function currentMedia() {
            const opt = currentOption();
            return (opt && opt.getAttribute('data-media')) || 'image';
        }

        function applySection() {
            const opt = currentOption();
            if (hint) {
                hint.textContent = (opt && opt.getAttribute('data-hint')) || 'Choose where this banner appears on the website.';
            }
            if (fields) {
                let list = {};
                try { list = JSON.parse((opt && opt.getAttribute('data-fields')) || '{}'); } catch (e) { list = {}; }
                fields.innerHTML = Object.keys(list).map(function (key) {
                    return '<li><strong>' + key + '</strong> — ' + list[key] + '</li>';
                }).join('');
                fields.hidden = !fields.innerHTML;
            }
            const key = opt ? opt.value : '';
            form.querySelectorAll('[data-show-for]').forEach(function (el) { el.hidden = el.getAttribute('data-show-for') !== key; });
            form.querySelectorAll('[data-hide-for]').forEach(function (el) { el.hidden = el.getAttribute('data-hide-for') === key; });
            const media = currentMedia();
            if (mediaCard) mediaCard.hidden = media === 'none';
            if (input) {
                input.setAttribute('accept', media === 'image_or_video'
                    ? '.png,.jpg,.jpeg,.webp,.mp4,.webm,.mov,image/png,image/jpeg,image/webp,video/mp4,video/webm,video/quicktime'
                    : '.png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp');
            }
            if (mediaNote) {
                mediaNote.textContent = media === 'image_or_video'
                    ? 'Each slide: a desktop video (mp4, webm, mov up to 50 MB) or image, plus an optional mobile image. For a video, the mobile image is also shown while the video loads.'
                    : 'Upload a JPG or PNG image (up to 50 MB).';
            }
        }

        if (section) {
            section.addEventListener('change', applySection);
            applySection();
        }

        // Live previews: new desktop file, new mobile file, and replacements on existing images.
        function showPreview(file, box) {
            if (!box) return;
            box.innerHTML = '';
            if (!file) return;
            const url = URL.createObjectURL(file);
            box.innerHTML = file.type.indexOf('video/') === 0
                ? '<video src="' + url + '" controls muted preload="metadata"></video><small>New: ' + file.name + '</small>'
                : '<img src="' + url + '" alt=""><small>New: ' + file.name + '</small>';
        }
        const mobileInput = document.getElementById('banner-mobile-image');
        const mobilePreview = document.getElementById('banner-mobile-preview');
        const mobileLabel = document.getElementById('banner-mobile-label');
        if (input) {
            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                showPreview(file, preview);
                if (mobileLabel) {
                    const isVideo = file && file.type.indexOf('video/') === 0;
                    mobileLabel.innerHTML = (isVideo ? 'Still image for the video (shown on phones and while it loads)' : 'Mobile image') + ' <span class="banner-variant__req">(optional)</span>';
                }
            });
        }
        if (mobileInput) {
            mobileInput.addEventListener('change', function () { showPreview(mobileInput.files && mobileInput.files[0], mobilePreview); });
        }
        form.querySelectorAll('input[type="file"][data-preview-into]').forEach(function (el) {
            el.addEventListener('change', function () { showPreview(el.files && el.files[0], form.querySelector(el.getAttribute('data-preview-into'))); });
        });

        if (window.FormValidator) {
            FormValidator.init(form, {
                title: [
                    { type: 'required', message: 'Banner title is required.' },
                    { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
                ],
                section: [
                    { type: 'required', message: 'Please select a section.' },
                ],
            });
        }

        form.addEventListener('submit', function (e) {
            if (currentMedia() === 'none') return;
            const hasExisting = form.querySelectorAll('.banner-existing-item').length > 0;
            const files = input && input.files ? input.files.length : 0;
            if (!hasExisting && files < 1) {
                e.preventDefault();
                const message = 'Please upload an image' + (currentMedia() === 'image_or_video' ? ' or video' : '') + ' for this section.';
                if (window.Toast) Toast.error(message); else alert(message);
            }
        });
    });
})();
