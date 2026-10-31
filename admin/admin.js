(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const csrf = document.currentScript?.dataset.csrf || '';

  /* mobilné menu */
  const side = $('#side'), mb = $('#menuBtn');
  mb?.addEventListener('click', () => { const o = side.classList.toggle('open'); mb.setAttribute('aria-expanded', o); });

  /* potvrdenie nebezpečných akcií */
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-confirm]');
    if (b && !confirm(b.dataset.confirm)) e.preventDefault();
  });

  /* upozornenie pri odchode s neuloženými zmenami */
  let dirty = false;
  $$('form.track').forEach(f => {
    f.addEventListener('input', () => { dirty = true; });
    f.addEventListener('change', () => { dirty = true; });
    f.addEventListener('submit', () => { dirty = false; });
  });
  addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  /* opakujúce sa riadky: [data-repeater] obsahuje .items, <template> a tlačidlo [data-add] */
  const renumber = list => $$(':scope > .item', list).forEach((it, i) => {
    const n = $('.num', it); if (n) n.textContent = i + 1;
  });
  $$('[data-repeater]').forEach(rep => {
    const list = $('.items', rep), tpl = $('template', rep);
    let counter = Date.now() % 100000;
    const add = () => {
      const html = tpl.innerHTML.replaceAll('__N__', String(counter++));
      const wrap = document.createElement('div'); wrap.innerHTML = html.trim();
      const node = wrap.firstElementChild; list.appendChild(node); renumber(list);
      initPickers(node); dirty = true;
      node.scrollIntoView({ behavior: 'smooth', block: 'center' });
      $('input:not([type=hidden]), textarea', node)?.focus({ preventScroll: true });
    };
    $('[data-add]', rep)?.addEventListener('click', add);
    rep.addEventListener('click', e => {
      const item = e.target.closest('.item'); if (!item || !list.contains(item)) return;
      if (e.target.closest('[data-del]')) {
        if (confirm(item.dataset.delMsg || 'Odstrániť túto položku?')) { item.remove(); renumber(list); dirty = true; }
      } else if (e.target.closest('[data-up]')) { item.previousElementSibling?.before(item); renumber(list); dirty = true; }
      else if (e.target.closest('[data-down]')) { item.nextElementSibling?.after(item); renumber(list); dirty = true; }
    });
    // živý názov v hlavičke riadku
    rep.addEventListener('input', e => {
      const item = e.target.closest('.item'); if (!item || !e.target.matches('[data-title]')) return;
      const h = $('.item-h strong', item); if (h) h.textContent = e.target.value.trim() || h.dataset.empty || 'Nová položka';
    });
    renumber(list);
  });

  /* výber a nahrávanie obrázkov */
  function initPickers(root = document) {
    $$('[data-picker]', root).forEach(p => {
      if (p.dataset.ready) return; p.dataset.ready = '1';
      const sel = $('select', p), prev = $('.pk-prev', p), file = $('input[type=file]', p), msg = $('.pk-msg', p);
      const sync = () => { prev.src = sel.value ? '../assets/media/' + sel.value : 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='; };
      sel.addEventListener('change', sync);
      file.addEventListener('change', async () => {
        const f = file.files[0]; if (!f) return;
        msg.className = 'pk-msg'; msg.textContent = 'Nahrávam…';
        const fd = new FormData(); fd.append('file', f);
        try {
          const r = await fetch('upload.php', { method: 'POST', body: fd, headers: { 'X-CSRF': csrf } });
          const j = await r.json();
          if (!j.ok) throw new Error(j.message);
          // nová možnosť vo všetkých výberoch na stránke, vybraná v tomto
          let og = $('optgroup[label="Nahrané"]', sel);
          $$('[data-picker] select').forEach(s => {
            let g = $('optgroup[label="Nahrané"]', s);
            if (!g) { g = document.createElement('optgroup'); g.label = 'Nahrané'; s.appendChild(g); }
            if (![...g.children].some(o => o.value === j.file)) { const o = document.createElement('option'); o.value = j.file; o.textContent = j.file; g.appendChild(o); }
          });
          sel.value = j.file; sync(); msg.textContent = 'Nahrané.';
          sel.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (ex) { msg.className = 'pk-msg err'; msg.textContent = ex.message || 'Nahrávanie zlyhalo.'; }
        file.value = '';
      });
    });
  }
  initPickers();

  /* nahrávanie videí: úvodný obrázok vytvorí prehliadač z prvého záberu, video sa pošle spolu s ním */
  const makePoster = file => new Promise((res, rej) => {
    const url = URL.createObjectURL(file), v = document.createElement('video');
    let done = false;
    const end = (fn, val) => { if (done) return; done = true; URL.revokeObjectURL(url); fn(val); };
    v.muted = true; v.playsInline = true; v.preload = 'metadata'; v.src = url;
    v.onerror = () => end(rej, new Error('poster'));
    v.onloadedmetadata = () => { v.currentTime = Math.min(1, (v.duration || 2) / 3); };
    v.onseeked = () => {
      const w = v.videoWidth, h = v.videoHeight; if (!w || !h) return end(rej, new Error('poster'));
      const s = Math.min(1, 720 / w), c = document.createElement('canvas');
      c.width = Math.round(w * s); c.height = Math.round(h * s);
      c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
      c.toBlob(b => b ? end(res, b) : end(rej, new Error('poster')), 'image/jpeg', .85);
    };
    setTimeout(() => end(rej, new Error('poster')), 20000);
  });
  $$('[data-vbulk]').forEach(box => {
    const input = $('input[type=file]', box), tiles = $('.tiles', box), tpl = $('template', box), msg = $('.vbulk-msg', box);
    let counter = Date.now() % 100000;
    const send = (file, poster) => new Promise((res, rej) => {
      const fd = new FormData(); fd.append('video', file); fd.append('poster', poster, 'poster.jpg');
      const x = new XMLHttpRequest(); x.open('POST', 'upload-video.php'); x.setRequestHeader('X-CSRF', csrf);
      x.upload.onprogress = e => { if (e.lengthComputable) msg.textContent = 'Nahrávam ' + file.name + '… ' + Math.round(e.loaded / e.total * 100) + ' %'; };
      x.onload = () => { let j; try { j = JSON.parse(x.responseText); } catch { return rej(new Error('Server vrátil neplatnú odpoveď (video je pravdepodobne väčšie než limit servera).')); } j.ok ? res(j) : rej(new Error(j.message)); };
      x.onerror = () => rej(new Error('Spojenie so serverom zlyhalo.'));
      x.send(fd);
    });
    input.addEventListener('change', async () => {
      const max = +box.dataset.max || 0;                 // limit servera (čítaný pri každom výbere)
      for (const f of [...input.files]) {
        if (max && f.size > max) { msg.textContent = f.name + ': video má ' + (f.size / 1048576).toFixed(1) + ' MB, server prijme najviac ' + (max / 1048576).toFixed(1) + ' MB. Zmenšite ho.'; continue; }
        msg.textContent = 'Pripravujem úvodný obrázok…';
        let poster;
        try { poster = await makePoster(f); } catch { msg.textContent = f.name + ': prehliadač video nevie prečítať. Uložte ho ako MP4 (H.264) a skúste znova.'; continue; }
        try {
          const j = await send(f, poster);
          const html = tpl.innerHTML.replaceAll('__N__', String(counter++)).replaceAll('__FILE__', j.file).replaceAll('__POSTER__', j.poster);
          const w = document.createElement('div'); w.innerHTML = html.trim();
          const tile = w.firstElementChild; $('input[type=text]', tile).value = j.title || '';
          tiles.appendChild(tile); dirty = true;
          msg.textContent = 'Video nahrané. Nezabudnite uložiť zmeny.';
        } catch (ex) { msg.textContent = f.name + ': ' + (ex.message || 'nahrávanie zlyhalo'); }
      }
      input.value = '';
    });
    tiles.addEventListener('click', e => {
      const t = e.target.closest('.tile'); if (!t) return;
      if (e.target.closest('[data-del]')) { t.remove(); dirty = true; }
      else if (e.target.closest('[data-up]')) { t.previousElementSibling?.before(t); dirty = true; }
      else if (e.target.closest('[data-down]')) { t.nextElementSibling?.after(t); dirty = true; }
    });
  });

  /* pridanie obrázkov do zoznamov (galéria, slider): [data-bulk-upload] vloží nové dlaždice z <template> */
  $$('[data-bulk]').forEach(box => {
    const input = $('input[type=file]', box), tiles = $('.tiles', box), tpl = $('template', box), msg = $('.bulk-msg', box);
    let counter = Date.now() % 100000;
    input.addEventListener('change', async () => {
      for (const f of [...input.files]) {
        msg.textContent = 'Nahrávam ' + f.name + '…';
        const fd = new FormData(); fd.append('file', f);
        try {
          const r = await fetch('upload.php', { method: 'POST', body: fd, headers: { 'X-CSRF': csrf } });
          const j = await r.json(); if (!j.ok) throw new Error(j.message);
          const html = tpl.innerHTML.replaceAll('__N__', String(counter++)).replaceAll('__FILE__', j.file).replaceAll('__URL__', j.url);
          const w = document.createElement('div'); w.innerHTML = html.trim(); tiles.appendChild(w.firstElementChild); dirty = true;
        } catch (ex) { msg.textContent = f.name + ': ' + (ex.message || 'zlyhalo'); input.value = ''; return; }
      }
      msg.textContent = 'Hotovo. Nezabudnite uložiť zmeny.'; input.value = '';
    });
    tiles.addEventListener('click', e => {
      const t = e.target.closest('.tile'); if (!t) return;
      if (e.target.closest('[data-del]')) { t.remove(); dirty = true; }
      else if (e.target.closest('[data-up]')) { t.previousElementSibling?.before(t); dirty = true; }
      else if (e.target.closest('[data-down]')) { t.nextElementSibling?.after(t); dirty = true; }
    });
  });
})();
