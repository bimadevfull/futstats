/* FutStats — interações do front-end (sem dependências) */
(function () {
  'use strict';
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  /* ---------- Tema claro/escuro ---------- */
  $('#themeToggle')?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    try { localStorage.setItem('futstats-theme', next); } catch (e) { /* storage indisponível */ }
  });

  /* ---------- Menu mobile ---------- */
  $('#menuToggle')?.addEventListener('click', () => $('#mainNav')?.classList.toggle('open'));

  /* ---------- Abas ---------- */
  $$('[data-tabs]').forEach((box) => {
    const buttons = $$('.tabs button', box);
    const show = (name) => {
      buttons.forEach((b) => b.classList.toggle('on', b.dataset.tab === name));
      $$('[data-panel]', box).forEach((p) => p.classList.toggle('on', p.dataset.panel === name));
      if (name === 'stats') animateBars(box);
    };
    buttons.forEach((b) => b.addEventListener('click', () => {
      show(b.dataset.tab);
      const url = new URL(location.href);
      url.searchParams.set('tab', b.dataset.tab);
      history.replaceState(null, '', url);
    }));
    const active = buttons.find((b) => b.classList.contains('on'));
    if (active?.dataset.tab === 'stats') animateBars(box);
  });

  function animateBars(root) {
    $$('.stat-bar i, .possession div', root).forEach((el) => {
      const w = el.style.width;
      el.style.width = '0';
      requestAnimationFrame(() => requestAnimationFrame(() => { el.style.width = w; }));
    });
  }

  /* ---------- Confirmação de exclusão ---------- */
  $$('form[data-confirm]').forEach((f) => f.addEventListener('submit', (ev) => {
    if (!window.confirm(f.dataset.confirm)) ev.preventDefault();
  }));

  /* ---------- Selects que enviam o formulário ---------- */
  $$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form.submit()));

  /* ---------- Filtro instantâneo de cards ---------- */
  $$('[data-filter]').forEach((input) => input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    $$(input.dataset.filter).forEach((card) => {
      card.style.display = !q || (card.dataset.search || card.textContent).toLowerCase().includes(q) ? '' : 'none';
    });
  }));

  /* ---------- Tabelas ordenáveis ---------- */
  $$('table[data-sortable]').forEach((table) => {
    const headers = $$('th', table);
    headers.forEach((th, idx) => {
      if (!th.dataset.sort) return;
      th.addEventListener('click', () => {
        const asc = !th.classList.contains('sorted-asc');
        headers.forEach((h) => h.classList.remove('sorted-asc', 'sorted-desc'));
        th.classList.add(asc ? 'sorted-asc' : 'sorted-desc');
        const tbody = table.tBodies[0];
        const val = (tr) => {
          const td = tr.children[idx];
          return td.dataset.value ?? td.textContent.trim();
        };
        const rows = Array.from(tbody.rows).sort((a, b) => {
          const x = val(a), y = val(b);
          const cmp = th.dataset.sort === 'num'
            ? (parseFloat(x) || 0) - (parseFloat(y) || 0)
            : x.localeCompare(y, 'pt-BR');
          return asc ? cmp : -cmp;
        });
        rows.forEach((r) => tbody.appendChild(r));
      });
    });
  });

  /* ---------- Prévia do escudo (form de time) ---------- */
  const badgeBox = $('[data-preview-badge]');
  if (badgeBox) {
    const crest = $('.crest', badgeBox);
    const nameIn = $('[data-preview-name]');
    const shortIn = $('[data-preview-short]');
    const label = $('[data-preview-label]');
    const update = () => {
      crest.style.setProperty('--c1', $('[data-preview-c1]').value);
      crest.style.setProperty('--c2', $('[data-preview-c2]').value);
      $('span', crest).textContent = (shortIn.value || nameIn.value.slice(0, 3) || 'NEW').toUpperCase().slice(0, 3);
      label.textContent = nameIn.value || 'Prévia do escudo';
    };
    $$('[data-preview-c1], [data-preview-c2], [data-preview-name], [data-preview-short]').forEach((i) => i.addEventListener('input', update));
  }

  /* ---------- Form de evento: filtra jogadores pelo time ---------- */
  const eventForm = $('[data-event-form]');
  if (eventForm) {
    const teamSel = $('[data-team-select]', eventForm);
    const typeSel = $('[data-event-type]', eventForm);
    const assistField = $('[data-assist-field]', eventForm);
    const sync = () => {
      const team = teamSel.value;
      $$('[data-player-select]', eventForm).forEach((sel) => {
        $$('optgroup', sel).forEach((g) => {
          const match = g.dataset.team === team;
          g.hidden = !match;
          g.disabled = !match;
        });
        if (sel.selectedOptions[0]?.parentElement?.disabled) sel.value = '';
      });
      assistField.style.display = typeSel.value === 'gol' ? '' : 'none';
    };
    teamSel.addEventListener('change', sync);
    typeSel.addEventListener('change', sync);
    sync();
  }

  /* ---------- Form de partida: campos conforme o status ---------- */
  const statusSel = $('[data-status-select]');
  if (statusSel) {
    const sync = () => {
      const s = statusSel.value;
      $$('[data-when-live]').forEach((el) => { el.style.display = s === 'live' ? '' : 'none'; });
      $$('[data-when-played]').forEach((el) => { el.style.display = s === 'scheduled' ? 'none' : ''; });
    };
    statusSel.addEventListener('change', sync);
    sync();
  }

  /* ---------- Esconde o flash depois de alguns segundos ---------- */
  const flash = $('.flash');
  if (flash) setTimeout(() => { flash.style.transition = 'opacity .4s'; flash.style.opacity = '0'; setTimeout(() => flash.remove(), 400); }, 4500);
})();
