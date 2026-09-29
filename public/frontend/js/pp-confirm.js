/**
 * Shared confirmation dialog — same UI as cart remove.
 * PPConfirm({ eyebrow, title, text, cancelLabel, confirmLabel })
 * returns Promise<boolean>
 */
(function (w) {
  'use strict';

  var seq = 0;

  function PPConfirm(options) {
    options = options || {};
    return new Promise(function (resolve) {
      var existing = document.getElementById('pp-shared-confirm');
      if (existing) existing.remove();

      seq += 1;
      var titleId = 'pp-confirm-title-' + seq;

      var overlay = document.createElement('div');
      overlay.id = 'pp-shared-confirm';
      overlay.className = 'pp-confirm';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.setAttribute('aria-labelledby', titleId);
      overlay.innerHTML =
        '<div class="pp-confirm__dialog">' +
          '<button type="button" class="pp-confirm__close" data-pp-confirm="cancel" aria-label="Close">&times;</button>' +
          '<p class="pp-confirm__eyebrow"></p>' +
          '<h3 id="' + titleId + '" class="pp-confirm__title"></h3>' +
          '<p class="pp-confirm__text"></p>' +
          '<div class="pp-confirm__actions">' +
            '<button type="button" class="pp-confirm__btn pp-confirm__btn--ghost" data-pp-confirm="cancel"></button>' +
            '<button type="button" class="pp-confirm__btn pp-confirm__btn--solid" data-pp-confirm="ok"></button>' +
          '</div>' +
        '</div>';

      overlay.querySelector('.pp-confirm__eyebrow').textContent = options.eyebrow || 'Please confirm';
      overlay.querySelector('.pp-confirm__title').textContent = options.title || 'Are you sure?';
      overlay.querySelector('.pp-confirm__text').textContent = options.text || '';
      overlay.querySelector('[data-pp-confirm="cancel"].pp-confirm__btn').textContent = options.cancelLabel || 'Cancel';
      overlay.querySelector('[data-pp-confirm="ok"]').textContent = options.confirmLabel || 'Confirm';

      function finish(ok) {
        document.removeEventListener('keydown', onKey);
        overlay.classList.remove('is-open');
        window.setTimeout(function () {
          if (overlay.parentNode) overlay.remove();
        }, 160);
        resolve(!!ok);
      }

      function onKey(e) {
        if (e.key === 'Escape') finish(false);
      }

      overlay.addEventListener('click', function (e) {
        var action = e.target.closest('[data-pp-confirm]');
        if (action) {
          finish(action.getAttribute('data-pp-confirm') === 'ok');
          return;
        }
        if (e.target === overlay) finish(false);
      });

      document.addEventListener('keydown', onKey);
      document.body.appendChild(overlay);
      requestAnimationFrame(function () {
        overlay.classList.add('is-open');
        var okBtn = overlay.querySelector('[data-pp-confirm="ok"]');
        if (okBtn) okBtn.focus();
      });
    });
  }

  w.PPConfirm = PPConfirm;
})(window);
