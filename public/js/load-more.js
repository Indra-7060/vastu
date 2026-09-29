/**
 * Server-side Load More (10 per page)
 * Expects JSON: { html, has_more, next_page }
 */
window.LoadMore = (function () {
    function init(options) {
        const list = document.querySelector(options.listSelector);
        const btn = document.querySelector(options.buttonSelector);
        const searchForm = document.querySelector(options.searchFormSelector || '#module-search-form');
        if (!list || !btn) return;

        let page = parseInt(btn.dataset.page || '1', 10);
        let loading = false;
        const baseUrl = btn.dataset.url || window.location.pathname;

        async function fetchPage(nextPage, replace) {
            if (loading) return;
            loading = true;
            btn.disabled = true;
            btn.textContent = 'Loading...';

            const params = new URLSearchParams(window.location.search);
            params.set('page', String(nextPage));
            params.set('ajax', '1');

            if (searchForm) {
                const fd = new FormData(searchForm);
                for (const [k, v] of fd.entries()) {
                    if (k !== 'page') params.set(k, v);
                }
            }

            try {
                const res = await fetch(`${baseUrl}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                const emptyState = document.getElementById('empty-state');

                if (replace) {
                    list.innerHTML = data.html || '';
                } else {
                    list.insertAdjacentHTML('beforeend', data.html || '');
                }

                page = nextPage;
                btn.dataset.page = String(page);

                if (data.has_more) {
                    btn.hidden = false;
                    btn.disabled = false;
                    btn.textContent = 'Load More';
                    btn.dataset.page = String(data.next_page - 1);
                } else {
                    btn.hidden = true;
                }

                if (!data.html || !String(data.html).trim()) {
                    if (replace) {
                        list.innerHTML = options.emptyHtml || '<div class="empty-state">No records found.</div>';
                    }
                    btn.hidden = true;
                    if (emptyState) emptyState.style.display = 'block';
                } else if (emptyState) {
                    emptyState.style.display = 'none';
                }
            } catch (err) {
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Load More';
                alert('Failed to load more records. Please try again.');
            } finally {
                loading = false;
            }
        }

        btn.addEventListener('click', () => {
            const next = parseInt(btn.dataset.page || '1', 10) + 1;
            fetchPage(next, false);
        });

        if (searchForm) {
            let timer = null;
            const searchInput = searchForm.querySelector('[name="search"]');

            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const url = new URL(window.location.href);
                const q = searchInput ? searchInput.value.trim() : '';
                if (q) url.searchParams.set('search', q);
                else url.searchParams.delete('search');
                url.searchParams.delete('page');
                window.history.replaceState({}, '', url.toString());
                btn.dataset.page = '0';
                fetchPage(1, true);
            });

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        searchForm.dispatchEvent(new Event('submit', { cancelable: true }));
                    }, 400);
                });
            }
        }
    }

    return { init };
})();
