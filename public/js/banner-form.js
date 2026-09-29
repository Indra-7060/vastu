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
                    ? '.png,.jpg,.jpeg,.mp4,.webm,.mov,image/png,image/jpeg,video/mp4,video/webm,video/quicktime'
                    : '.png,.jpg,.jpeg,image/png,image/jpeg');
            }
            if (mediaNote) {
                mediaNote.textContent = media === 'image_or_video'
                    ? 'Upload a video (mp4, webm, mov up to 50 MB) or a wide image. For a video, also add a still image — it is shown while the video loads.'
                    : 'Upload a JPG or PNG image (up to 4 MB).';
            }
        }

        if (section) {
            section.addEventListener('change', applySection);
            applySection();
        }

        if (input && preview && meta) {
            input.addEventListener('change', function () {
                preview.innerHTML = '';
                meta.innerHTML = '';
                Array.from(input.files || []).forEach(function (file, index) {
                    const url = URL.createObjectURL(file);
                    const isVideo = file.type.indexOf('video/') === 0;
                    const thumb = document.createElement('div');
                    thumb.className = 'gallery-thumb';
                    thumb.innerHTML = isVideo
                        ? '<video src="' + url + '" controls muted preload="metadata" style="width:100%;height:100%;object-fit:cover;"></video>'
                        : '<img src="' + url + '" alt="">';
                    preview.appendChild(thumb);

                    const row = document.createElement('div');
                    row.className = 'banner-meta-row';
                    row.innerHTML =
                        '<div class="form-group"><label>' + (isVideo ? 'Still image for the video (recommended)' : 'Mobile image (optional)') + '</label>' +
                        '<input type="file" name="mobile_images[' + index + ']" accept=".png,.jpg,.jpeg,image/png,image/jpeg"></div>';
                    meta.appendChild(row);
                });
            });
        }

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
