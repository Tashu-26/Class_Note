// assets/js/app.js — NoteOrg frontend interactions

/* ── Dark mode ─────────────────────────────────────────── */
(function () {
  const saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();

function toggleTheme() {
  const curr = document.documentElement.getAttribute('data-theme');
  const next = curr === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
  const btn = document.querySelector('.theme-toggle');
  if (btn) btn.textContent = next === 'dark' ? '☀ Light' : '☾ Dark';
}

/* ── Flash messages ────────────────────────────────────── */
function flash(msg, type = 'success') {
  const el = document.createElement('div');
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  document.querySelector('.main')?.prepend(el);
  setTimeout(() => el.remove(), 3500);
}

/* ── Favorite toggle ───────────────────────────────────── */
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.star-btn[data-note-id]');
  if (!btn) return;
  const id = btn.dataset.noteId;
  try {
    const res = await fetch('api/toggle_favorite.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ note_id: id, csrf: window._csrf || '' })
    });
    const data = await res.json();
    if (data.ok) {
      btn.classList.toggle('active', data.is_favorite);
      btn.textContent = data.is_favorite ? '★' : '☆';
    }
  } catch (_) { flash('Something went wrong.', 'error'); }
});

/* ── File upload drag-and-drop ─────────────────────────── */
function initUploadZone(zoneEl, inputEl) {
  if (!zoneEl || !inputEl) return;
  zoneEl.addEventListener('click', () => inputEl.click());
  zoneEl.addEventListener('dragover', (e) => { e.preventDefault(); zoneEl.classList.add('drag-over'); });
  zoneEl.addEventListener('dragleave',  () => zoneEl.classList.remove('drag-over'));
  zoneEl.addEventListener('drop', (e) => {
    e.preventDefault(); zoneEl.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
      inputEl.files = e.dataTransfer.files;
      inputEl.dispatchEvent(new Event('change'));
    }
  });
  inputEl.addEventListener('change', () => {
    const file = inputEl.files[0];
    if (!file) return;
    const p = zoneEl.querySelector('p');
    if (p) p.textContent = `✓  ${file.name}`;
  });
}

/* ── Search live filter (client-side fallback) ─────────── */
function initSearch(inputEl, cardsSelector) {
  if (!inputEl) return;
  inputEl.addEventListener('input', () => {
    const q = inputEl.value.toLowerCase().trim();
    document.querySelectorAll(cardsSelector).forEach(card => {
      const text = card.textContent.toLowerCase();
      card.style.display = !q || text.includes(q) ? '' : 'none';
    });
  });
}

/* ── Filter pills ──────────────────────────────────────── */
document.addEventListener('click', (e) => {
  const pill = e.target.closest('.filter-pill[data-filter]');
  if (!pill) return;
  const group = pill.closest('.filter-row');
  group?.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
  pill.classList.add('active');
  const filterVal = pill.dataset.filter;
  document.querySelectorAll('.note-card, .note-list-item').forEach(card => {
    if (filterVal === 'all') { card.style.display = ''; return; }
    const tags   = [...card.querySelectorAll('.tag')].map(t => t.textContent.toLowerCase());
    const isFav  = card.querySelector('.star-btn.active');
    if (filterVal === 'favorites') {
      card.style.display = isFav ? '' : 'none';
    } else {
      card.style.display = tags.includes(filterVal) ? '' : 'none';
    }
  });
});

/* ── Delete note confirmation ──────────────────────────── */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.delete-note-btn');
  if (!btn) return;
  if (!confirm('Delete this note? This cannot be undone.')) e.preventDefault();
});

/* ── Auto-dismiss alerts ───────────────────────────────── */
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => el.style.opacity = '0', 3000);
  setTimeout(() => el.remove(), 3500);
});

/* ── Init on DOM ready ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initUploadZone(
    document.querySelector('.upload-zone'),
    document.querySelector('#file-input')
  );
  initSearch(
    document.querySelector('#search-input'),
    '.note-card, .note-list-item'
  );
  const themeBtn = document.querySelector('.theme-toggle');
  const curr = document.documentElement.getAttribute('data-theme');
  if (themeBtn) themeBtn.textContent = curr === 'dark' ? '☀ Light' : '☾ Dark';
});
