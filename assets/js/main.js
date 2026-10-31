(() => {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fine = matchMedia('(hover: hover) and (pointer: fine)').matches;

  /* Oznam hore: odroluje sa preč spolu so stránkou, hlavička sa posúva pod ním; krížik ho skryje do konca návštevy */
  const bar = $('[data-announce]');
  if (bar) {
    const sig = bar.textContent.trim().slice(0, 120);
    let closed = false;
    try { closed = sessionStorage.getItem('op-announce-closed') === sig; } catch {}
    const sync = () => {
      const h = closed ? 0 : bar.offsetHeight;
      document.body.style.setProperty('--bar-h', h + 'px');
      document.body.style.setProperty('--bar-off', Math.max(0, h - scrollY) + 'px');
    };
    if (closed) bar.hidden = true;
    $('.announce-close', bar).addEventListener('click', () => {
      closed = true; bar.hidden = true;
      try { sessionStorage.setItem('op-announce-closed', sig); } catch {}
      sync();
    });
    addEventListener('scroll', sync, { passive: true }); addEventListener('resize', sync); sync();
  }

  /* Header */
  const header = $('[data-header]');
  const onScroll = () => header.classList.toggle('scrolled', scrollY > 30);
  onScroll(); addEventListener('scroll', onScroll, { passive: true });

  const burger = $('[data-burger]'), nav = $('#nav');
  burger?.addEventListener('click', () => {
    const open = burger.getAttribute('aria-expanded') !== 'true';
    burger.setAttribute('aria-expanded', open);
    nav.classList.toggle('open', open);
    document.body.classList.toggle('menu-open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  /* pri zväčšení okna na šírku počítača sa mobilné menu zavrie a obnoví sa posúvanie */
  matchMedia('(min-width: 1081px)').addEventListener('change', e => {
    if (!e.matches) return;
    burger?.setAttribute('aria-expanded', 'false'); nav?.classList.remove('open');
    document.body.classList.remove('menu-open'); document.body.style.overflow = '';
  });
  $$('#nav a').forEach(a => a.addEventListener('click', () => {
    burger.setAttribute('aria-expanded', 'false'); nav.classList.remove('open');
    document.body.classList.remove('menu-open'); document.body.style.overflow = '';
  }));

  /* Rozbaľovacie menu: na počítači sa otvára myšou/klávesnicou, na mobile tlačidlom so šípkou */
  const drops = $$('.has-drop');
  const closeDrops = except => drops.forEach(d => {
    if (d === except) return;
    d.classList.remove('open'); $('.drop-toggle', d).setAttribute('aria-expanded', 'false');
  });
  drops.forEach(d => {
    const t = $('.drop-toggle', d);
    t.addEventListener('click', e => {
      e.stopPropagation();
      const open = !d.classList.contains('open');
      closeDrops(d);
      d.classList.toggle('open', open); t.setAttribute('aria-expanded', open);
    });
  });
  document.addEventListener('click', e => { if (!e.target.closest('.has-drop')) closeDrops(); });
  addEventListener('keydown', e => { if (e.key === 'Escape') closeDrops(); });

  /* Kým je otvorené modálne okno (dialog), stránka pod ním sa nedá posúvať */
  const dialogs = $$('dialog');
  if (dialogs.length) {
    const root = document.documentElement;
    const syncLock = () => {
      const open = dialogs.some(d => d.open);
      if (open === root.classList.contains('modal-open')) return;
      if (open) root.style.setProperty('--sbw', (innerWidth - root.clientWidth) + 'px');   // šírka posuvníka, aby obsah „neskočil“
      root.classList.toggle('modal-open', open);
    };
    const mo = new MutationObserver(syncLock);
    dialogs.forEach(d => mo.observe(d, { attributes: true, attributeFilter: ['open'] }));
  }

  /* Reveal on scroll */
  const revealEls = $$('.reveal');
  if ('IntersectionObserver' in window && !reduce) {
    const io = new IntersectionObserver(es => es.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
    }), { threshold: .12, rootMargin: '0px 0px -6% 0px' });
    revealEls.forEach(el => io.observe(el));
  } else revealEls.forEach(el => el.classList.add('in'));

  /* 3D tilt cards */
  if (fine && !reduce) {
    $$('.tilt').forEach(el => {
      const max = +(el.dataset.tilt || 9);
      el.addEventListener('pointermove', e => {
        const r = el.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        el.classList.add('is-tilting');
        el.style.setProperty('--ry', ((x - .5) * max * 2).toFixed(2) + 'deg');
        el.style.setProperty('--rx', ((.5 - y) * max * 2).toFixed(2) + 'deg');
        el.style.setProperty('--gx', (x * 100).toFixed(1) + '%');
        el.style.setProperty('--gy', (y * 100).toFixed(1) + '%');
      });
      el.addEventListener('pointerleave', () => {
        el.classList.remove('is-tilting');
        el.style.setProperty('--rx', '0deg'); el.style.setProperty('--ry', '0deg');
      });
    });

    /* hero scene follows the pointer */
    const scene = $('.scene');
    if (scene) {
      const hero = scene.closest('.hero');
      hero.addEventListener('pointermove', e => {
        const r = hero.getBoundingClientRect();
        scene.style.setProperty('--ry', (((e.clientX - r.left) / r.width - .5) * 16).toFixed(2) + 'deg');
        scene.style.setProperty('--rx', ((.5 - (e.clientY - r.top) / r.height) * 12).toFixed(2) + 'deg');
      });
      hero.addEventListener('pointerleave', () => { scene.style.setProperty('--rx', '0deg'); scene.style.setProperty('--ry', '0deg'); });
    }
  }

  /* Hero slider */
  $$('[data-slider]').forEach(root => {
    const slides = $$('.slide', root), tabs = $$('.s-dots button', root);
    const DUR = 3500, ANIM = 1500;
    let cur = 0, timer = null, busy = false, hover = false;
    if (reduce) root.classList.add('no-auto');

    const go = (n, dir) => {
      n = (n + slides.length) % slides.length;
      if (n === cur || busy) return;
      busy = true;
      const prev = slides[cur], next = slides[n];
      root.classList.add('no-anim');
      root.classList.toggle('dir-back', dir < 0);
      next.getBoundingClientRect();                 // reflow: nový záber začne od správnej strany
      root.classList.remove('no-anim');
      prev.classList.remove('is-active'); prev.classList.add('is-prev');
      next.classList.add('is-active');
      slides.forEach((s, i) => s.setAttribute('aria-hidden', i !== n));
      tabs.forEach((t, i) => t.setAttribute('aria-selected', i === n));
      cur = n;
      setTimeout(() => { prev.classList.remove('is-prev'); busy = false; }, reduce ? 0 : ANIM);
      schedule();
    };
    const schedule = () => {
      clearTimeout(timer);
      if (reduce || hover || document.hidden) return;
      timer = setTimeout(() => go(cur + 1, 1), DUR);
    };

    tabs.forEach((t, i) => t.addEventListener('click', () => go(i, i > cur ? 1 : -1)));

    /* pauza pri hoveri, aj časovač bodky */
    root.addEventListener('pointerenter', e => { if (e.pointerType === 'mouse') { hover = true; root.classList.add('paused'); clearTimeout(timer); } });
    root.addEventListener('pointerleave', e => { if (e.pointerType === 'mouse') { hover = false; root.classList.remove('paused'); restart(); } });
    document.addEventListener('visibilitychange', () => document.hidden ? clearTimeout(timer) : restart());
    const restart = () => {
      // zopakuj animáciu bodky, aby sedela s novým časovačom
      const t = tabs[cur]; t.setAttribute('aria-selected', 'false'); t.getBoundingClientRect(); t.setAttribute('aria-selected', 'true');
      schedule();
    };

    /* swipe */
    let x0 = null;
    root.addEventListener('pointerdown', e => { x0 = e.clientX; });
    root.addEventListener('pointerup', e => {
      if (x0 === null) return;
      const dx = e.clientX - x0; x0 = null;
      if (Math.abs(dx) > 40) go(cur + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1);
    });
    root.addEventListener('pointercancel', () => { x0 = null; });
    root.setAttribute('tabindex', '0');
    root.addEventListener('keydown', e => {
      if (e.key === 'ArrowRight') go(cur + 1, 1); else if (e.key === 'ArrowLeft') go(cur - 1, -1);
    });
    schedule();
  });

  /* Three.js sa načítava na požiadanie a len raz (zdieľa ho pozadie sekcií aj model reformeru) */
  const loadScript = src => new Promise((res, rej) => { const s = document.createElement('script'); s.src = src; s.onload = res; s.onerror = rej; document.head.appendChild(s); });
  const loadThree = () => (window.__opThree = window.__opThree || loadScript('assets/js/vendor/three.min.js'));
  const hasWebGL = (() => { try { const c = document.createElement('canvas'); return !!(c.getContext('webgl2') || c.getContext('webgl')); } catch { return false; } })();

  /* Interaktívny 3D model reformeru (stránka Cvičenia) */
  const r3d = $('[data-reformer3d]');
  if (r3d) {
    if (!hasWebGL) r3d.classList.add('no-3d');
    else {
      const rio = new IntersectionObserver(([e]) => {
        if (!e.isIntersecting) return;
        rio.disconnect();
        loadThree().then(() => loadScript('assets/js/reformer3d.js?v=1')).then(() => window.OPReformer(r3d)).catch(() => r3d.classList.add('no-3d'));
      }, { rootMargin: '600px' });
      rio.observe(r3d);
    }
  }

  /* 3D scéna (Three.js) – načíta sa až keď je sekcia blízko viewportu */
  const canvases = $$('canvas[data-gl]');
  if (canvases.length) {
    let lib = null;
    const ensure = () => lib || (lib = loadThree().then(() => loadScript('assets/js/gl.js?v=1')));
    if (hasWebGL && !matchMedia('(max-width: 820px)').matches) { /* na mobile sa 600 kB Three.js kvôli dekorácii nesťahuje */
      const gio = new IntersectionObserver(es => es.forEach(e => {
        if (!e.isIntersecting) return;
        gio.unobserve(e.target);
        ensure().then(() => { window.OPGL(e.target, e.target.dataset.gl); e.target.classList.add('ready'); }).catch(() => {});
      }), { rootMargin: '400px' });
      canvases.forEach(c => gio.observe(c));
    }
  }

  /* 3D naklonenie fotiek pri scrollovaní */
  const frames = $$('.split-media .frame:not(.zoomable)');
  if (frames.length && !reduce && CSS.supports('rotate', '0 1 0 10deg')) {
    let busy = false;
    const spin = () => {
      frames.forEach(f => {
        const r = f.getBoundingClientRect();
        if (r.bottom < 0 || r.top > innerHeight) return;
        const p = Math.max(-1, Math.min(1, (r.top + r.height / 2 - innerHeight / 2) / (innerHeight / 2)));
        const side = f.closest('.split.rev') ? -1 : 1;
        f.style.rotate = `0.35 1 0 ${(p * 4 * side).toFixed(2)}deg`;
      });
      busy = false;
    };
    addEventListener('scroll', () => { if (!busy) { busy = true; requestAnimationFrame(spin); } }, { passive: true });
    spin();
  }

  /* Všeobecné dialógy: [data-open-dialog="#id"] otvorí <dialog>, krížik alebo klik mimo ho zavrie */
  $$('[data-open-dialog]').forEach(btn => {
    const dlg = $(btn.dataset.openDialog);
    if (!dlg) return;
    btn.addEventListener('click', () => dlg.showModal());
    $('.close', dlg)?.addEventListener('click', () => dlg.close());
    dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
  });

  /* Modálne okno pre fotky a videá (stránka Cvičenia) */
  const modal = $('#mediaModal');
  if (modal) {
    const box = $('.mm-body', modal);
    const open = (kind, src, alt) => {
      box.innerHTML = '';
      const el = document.createElement(kind === 'video' ? 'video' : 'img');
      el.src = src;
      if (kind === 'video') { Object.assign(el, { controls: true, autoplay: true, loop: true, playsInline: true }); }
      else el.alt = alt || '';
      box.appendChild(el);
      modal.showModal();
      if (kind === 'video') el.play().catch(() => {});
    };
    const activate = (el, fn) => {
      el.addEventListener('click', e => { if (!e.target.closest('.sound')) fn(); });
      el.addEventListener('keydown', e => { if ((e.key === 'Enter' || e.key === ' ') && e.target === el) { e.preventDefault(); fn(); } });
    };
    $$('[data-modal-img]').forEach(el => activate(el, () => open('img', el.dataset.modalImg, $('img', el)?.alt)));
    $$('[data-modal-video]').forEach(el => activate(el, () => {
      $('video', el)?.pause(); el.classList.remove('playing');
      open('video', el.dataset.modalVideo);
    }));
    $('.mm-close', modal).addEventListener('click', () => modal.close());
    modal.addEventListener('click', e => { if (e.target === modal || e.target === box) modal.close(); });
    modal.addEventListener('close', () => { box.innerHTML = ''; });
  }

  /* Slider recenzií: šípky, bodky, swipe (natívne scroll-snap), pomalé automatické posúvanie */
  $$('[data-rev]').forEach(track => {
    const root = track.closest('.reviews');
    const cards = $$('.rev-card', track), dotsEl = $('[data-rev-dots]', root);
    const step = () => cards[0].getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0);
    const index = () => Math.round(track.scrollLeft / step());
    const maxIdx = () => Math.max(0, Math.round((track.scrollWidth - track.clientWidth) / step()));
    const go = i => track.scrollTo({ left: Math.max(0, Math.min(maxIdx(), i)) * step(), behavior: reduce ? 'auto' : 'smooth' });
    const mark = () => $$('button', dotsEl).forEach((b, i) => b.setAttribute('aria-current', i === index()));
    const buildDots = () => {
      dotsEl.innerHTML = Array.from({ length: maxIdx() + 1 }, (_, i) => `<button type="button" tabindex="-1" data-i="${i}"><i></i></button>`).join('');
      mark();
    };
    dotsEl.addEventListener('click', e => { const b = e.target.closest('button'); if (b) go(+b.dataset.i); });
    $('[data-rev-prev]', root).addEventListener('click', () => go(index() - 1));
    $('[data-rev-next]', root).addEventListener('click', () => go(index() + 1));
    track.addEventListener('scroll', () => requestAnimationFrame(mark), { passive: true });
    track.addEventListener('keydown', e => { if (e.key === 'ArrowRight') go(index() + 1); else if (e.key === 'ArrowLeft') go(index() - 1); });
    addEventListener('resize', buildDots);
    buildDots();

    /* automatické posúvanie: len keď je sekcia vidno, nie pri hoveri/zameraní, nie pri „znížený pohyb" */
    if (reduce) return;
    let timer = null, hovered = false, visible = false;
    const tick = () => { if (!hovered && visible) go(index() >= maxIdx() ? 0 : index() + 1); };
    const start = () => { clearInterval(timer); timer = setInterval(tick, 5500); };
    root.addEventListener('pointerenter', () => { hovered = true; });
    root.addEventListener('pointerleave', () => { hovered = false; });
    root.addEventListener('focusin', () => { hovered = true; });
    root.addEventListener('focusout', () => { hovered = false; });
    new IntersectionObserver(([en]) => { visible = en.isIntersecting; }, { threshold: .3 }).observe(root);
    start();
  });

  /* Parallax */
  const par = $$('.parallax');
  if (par.length && !reduce) {
    let tick = false;
    const run = () => {
      par.forEach(el => {
        const r = el.getBoundingClientRect();
        if (r.bottom < -200 || r.top > innerHeight + 200) return;
        const p = (r.top + r.height / 2 - innerHeight / 2) * (+el.dataset.speed || .08);
        el.style.transform = `translate3d(0, ${(-p).toFixed(1)}px, 0)`;
      });
      tick = false;
    };
    addEventListener('scroll', () => { if (!tick) { tick = true; requestAnimationFrame(run); } }, { passive: true });
    run();
  }

  /* Rad videí: šípky na posúvanie (zobrazia sa len keď sa rad dá posúvať) */
  $$('.video-row').forEach(row => {
    const wrap = document.createElement('div'); wrap.className = 'vr-wrap';
    row.parentNode.insertBefore(wrap, row); wrap.appendChild(row);
    const mk = (cls, label, d) => {
      const b = document.createElement('button'); b.type = 'button'; b.className = 'vr-btn ' + cls; b.setAttribute('aria-label', label);
      b.innerHTML = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
      return b;
    };
    const prev = mk('vr-prev', 'Predchádzajúce video', 'm15 5-7 7 7 7'), next = mk('vr-next', 'Ďalšie video', 'm9 5 7 7-7 7');
    wrap.append(prev, next);
    const step = () => { const c = row.firstElementChild; return (c ? c.getBoundingClientRect().width : 280) + (parseFloat(getComputedStyle(row).columnGap) || 0); };
    const update = () => {
      const max = row.scrollWidth - row.clientWidth;
      wrap.classList.toggle('can-scroll', max > 2);
      prev.disabled = row.scrollLeft <= 2; next.disabled = row.scrollLeft >= max - 2;
    };
    prev.addEventListener('click', () => row.scrollBy({ left: -step(), behavior: reduce ? 'auto' : 'smooth' }));
    next.addEventListener('click', () => row.scrollBy({ left: step(), behavior: reduce ? 'auto' : 'smooth' }));
    row.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
    new ResizeObserver(update).observe(row); update();
  });

  /* Videos: play when visible, tap to toggle */
  const videos = $$('.video-card video');
  if (videos.length) {
    const setSound = (card, on) => {
      const v = $('video', card), b = $('.sound', card);
      v.muted = !on;
      card.classList.toggle('sound-on', on);
      b?.setAttribute('aria-pressed', on);
      b?.setAttribute('aria-label', on ? 'Vypnúť zvuk' : 'Zapnúť zvuk');
    };
    const vio = new IntersectionObserver(es => es.forEach(e => {
      const v = e.target, card = v.closest('.video-card');
      if (e.isIntersecting && e.intersectionRatio > .55 && !reduce) {
        v.play().then(() => card.classList.add('playing')).catch(() => {});
      } else { v.pause(); card.classList.remove('playing'); setSound(card, false); }
    }), { threshold: [0, .55, 1] });
    videos.forEach(v => vio.observe(v));
    $$('.video-card').forEach(c => {
      /* kliknutie na video = pauza/spustenie */
      c.addEventListener('click', e => {
        if (e.target.closest('.sound') || c.hasAttribute('data-modal-video') || c.hasAttribute('data-lb-video')) return;
        const v = $('video', c);
        if (v.paused) { v.play(); c.classList.add('playing'); } else { v.pause(); c.classList.remove('playing'); }
      });
      /* tlačidlo zvuku: zvuk môže hrať len jedno video, prehliadač ho povolí až po kliknutí */
      $('.sound', c)?.addEventListener('click', e => {
        e.stopPropagation();
        const on = !c.classList.contains('sound-on');
        $$('.video-card').forEach(o => setSound(o, false));
        setSound(c, on);
        if (on) { const v = $('video', c); if (v.paused) { v.play(); c.classList.add('playing'); } }
      });
    });
  }

  /* ---------- Booking (ukážka: rezervácie sa zatiaľ neukladajú) ---------- */
  const OP = window.OP || { classes: [], schedule: [] };
  const classes = Object.fromEntries(OP.classes.map(c => [c.id, c]));
  const DAYS = ['Ne', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So'];
  const DAY_NAMES = ['nedeľa', 'pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota'];
  const MONTHS = ['jan', 'feb', 'mar', 'apr', 'máj', 'jún', 'júl', 'aug', 'sep', 'okt', 'nov', 'dec'];
  const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const hash = s => { let h = 2166136261; for (const ch of s) { h ^= ch.charCodeAt(0); h = Math.imul(h, 16777619); } return (h >>> 0); };

  /* Dopredu sa dá rezervovať 12 mesiacov (aktuálny + ďalších 11); mení sa tu */
  const BOOK_MONTHS = Math.max(1, Math.min(24, +OP.bookMonths || 12));
  const MONTH_NAMES = ['január', 'február', 'marec', 'apríl', 'máj', 'jún', 'júl', 'august', 'september', 'október', 'november', 'december'];
  const today0 = (() => { const n = new Date(); return new Date(n.getFullYear(), n.getMonth(), n.getDate()); })();
  const horizonEnd = new Date(today0.getFullYear(), today0.getMonth() + BOOK_MONTHS, 0);   // posledný deň posledného mesiaca

  function buildSessions(daysAhead) {
    const out = [], now = new Date();
    for (let i = 0; i < daysAhead; i++) {
      const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() + i);
      const wd = d.getDay() === 0 ? 7 : d.getDay();
      OP.schedule.filter(s => s.day === wd).forEach(s => {
        const [hh, mm] = s.time.split(':').map(Number);
        const start = new Date(d.getFullYear(), d.getMonth(), d.getDate(), hh, mm);
        if (start < now) return;
        const c = classes[s.class]; if (!c) return;
        const key = `${iso(d)}|${s.time}|${s.class}`;
        const r = hash(key) % 100;
        const taken = r < 22 ? c.capacity : Math.min(c.capacity - 1, Math.floor((r / 100) * c.capacity));
        out.push({ key, date: iso(d), d, time: s.time, cls: c, taken });
      });
    }
    return out.sort((a, b) => a.date.localeCompare(b.date) || a.time.localeCompare(b.time));
  }
  const sessions = buildSessions(Math.round((horizonEnd - today0) / 864e5) + 1);

  /* Hero: najbližšie voľné termíny */
  $$('[data-next]').forEach(nextBox => {
    const free = sessions.filter(s => s.taken < s.cls.capacity).slice(0, 3);
    nextBox.innerHTML = free.map(s => {
      const left = s.cls.capacity - s.taken;
      return `<a class="next-item" href="rezervacia.php?date=${s.date}&class=${s.cls.id}">
        <span class="when"><b>${DAYS[s.d.getDay()]} ${s.d.getDate()}.${s.d.getMonth() + 1}.</b> ${s.time}</span>
        <span>${s.cls.name}</span><span class="left">${s.cls.capacity === 1 ? 'individuálne' : left === 1 ? 'posledné miesto' : left + ' voľné'}</span></a>`;
    }).join('') || '<p style="margin:0">Aktuálne nie sú vypísané termíny.</p>';
  });

  const bk = $('#booking');
  if (bk) {
    const params = new URLSearchParams(location.search);
    const state = { cls: classes[params.get('class')] ? params.get('class') : 'all', date: params.get('date') || iso(today0) };
    const fEl = $('#filters'), dEl = $('#days'), sEl = $('#sessions'), titleEl = $('#calTitle');
    const prevBtn = $('[data-month-prev]'), nextBtn = $('[data-month-next]'), todayBtn = $('#calToday');

    /* platný dátum musí byť v rozsahu dnes … koniec horizontu */
    const parse = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    let sel = parse(state.date);
    if (isNaN(sel) || sel < today0 || sel > horizonEnd) sel = today0;
    state.date = iso(sel);
    const view = { y: sel.getFullYear(), m: sel.getMonth() };      // zobrazený mesiac
    const monthIdx = (y, m) => y * 12 + m;
    const minIdx = monthIdx(today0.getFullYear(), today0.getMonth()), maxIdx = monthIdx(horizonEnd.getFullYear(), horizonEnd.getMonth());

    const renderFilters = () => {
      const inMonth = s => s.d.getFullYear() === view.y && s.d.getMonth() === view.m;
      const n = id => sessions.filter(s => inMonth(s) && (id === 'all' || s.cls.id === id)).length;
      fEl.innerHTML = [{ id: 'all', name: 'Všetky cvičenia', blurb: 'Zobraziť všetky lekcie', color: '#5a8a84' }, ...OP.classes].map(c =>
        `<button class="filter" style="--c:${c.color}" aria-pressed="${state.cls === c.id}" data-id="${c.id}"><i></i><span class="f-text"><b>${c.name}</b>${c.blurb ? `<small>${c.blurb}</small>` : ''}</span><em class="cnt">${n(c.id)}</em></button>`).join('');
    };
    const renderDays = () => {
      let start = new Date(view.y, view.m, 1), end = new Date(view.y, view.m + 1, 0);
      if (start < today0) start = today0;
      if (monthIdx(view.y, view.m) === minIdx) {                 // v aktuálnom mesiaci ukáž aspoň 14 dní dopredu
        const min14 = new Date(today0.getFullYear(), today0.getMonth(), today0.getDate() + 13);
        if (end < min14) end = min14;
      }
      if (end > horizonEnd) end = horizonEnd;
      const days = [];
      for (let d = new Date(start); d <= end; d = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 1)) days.push(d);
      const has = new Set(sessions.filter(s => state.cls === 'all' || s.cls.id === state.cls).map(s => s.date));
      dEl.innerHTML = days.map(d => `<button class="day${has.has(iso(d)) ? '' : ' off'}" aria-pressed="${iso(d) === state.date}" data-date="${iso(d)}"><small>${DAYS[d.getDay()]}</small><b>${d.getDate()}</b><small>${MONTHS[d.getMonth()]}</small></button>`).join('');
      titleEl.textContent = `${MONTH_NAMES[view.m]} ${view.y}`;
      const idx = monthIdx(view.y, view.m);
      prevBtn.disabled = idx <= minIdx; nextBtn.disabled = idx >= maxIdx;
      $('.day[aria-pressed="true"]', dEl)?.scrollIntoView({ inline: 'center', block: 'nearest' });
    };
    /* prepnutie mesiaca: vyberie sa prvý dostupný deň (dnes v aktuálnom mesiaci, inak 1.) */
    const goMonth = (y, m) => {
      view.y = y; view.m = m;
      const first = new Date(y, m, 1), d = first < today0 ? today0 : first;
      state.date = iso(d);
      renderFilters(); renderDays(); renderSessions();
    };
    prevBtn.addEventListener('click', () => { const d = new Date(view.y, view.m - 1, 1); goMonth(d.getFullYear(), d.getMonth()); });
    nextBtn.addEventListener('click', () => { const d = new Date(view.y, view.m + 1, 1); goMonth(d.getFullYear(), d.getMonth()); });
    todayBtn.addEventListener('click', () => { goMonth(today0.getFullYear(), today0.getMonth()); });
    const renderSessions = () => {
      const list = sessions.filter(s => s.date === state.date && (state.cls === 'all' || s.cls.id === state.cls));
      sEl.innerHTML = list.length ? list.map((s, i) => {
        const full = s.taken >= s.cls.capacity, left = s.cls.capacity - s.taken;
        const dots = s.cls.capacity <= 12 ? `<div class="spots" aria-hidden="true">${Array.from({ length: s.cls.capacity }, (_, k) => `<span class="${k < s.taken ? 'taken' : ''}"></span>`).join('')}</div>` : '';
        return `<div class="session ${full ? 'full' : ''}" style="--c:${s.cls.color};animation-delay:${i * 60}ms">
          <div class="time">${s.time}</div>
          <div class="info"><b><i></i>${s.cls.name}</b><small>${s.cls.duration} min · ${full ? 'obsadené' : s.cls.capacity === 1 ? 'individuálny tréning' : left === 1 ? 'posledné miesto' : left + ' voľných z ' + s.cls.capacity}</small>${s.cls.capacity > 1 ? dots : ''}</div>
          <button class="btn btn-sm ${full ? 'btn-ghost-dark' : 'btn-primary'}" data-key="${s.key}">${full ? 'Čakacia listina' : 'Rezervovať'}</button></div>`;
      }).join('') : '<p class="empty">V tento deň nie sú vypísané žiadne lekcie so zvoleným filtrom.</p>';
    };
    renderFilters(); renderDays(); renderSessions();
    fEl.addEventListener('click', e => { const b = e.target.closest('.filter'); if (!b) return; state.cls = b.dataset.id; renderFilters(); renderDays(); renderSessions(); });
    dEl.addEventListener('click', e => {
      const b = e.target.closest('.day'); if (!b) return;
      state.date = b.dataset.date;
      const d = parse(state.date);
      if (d.getFullYear() !== view.y || d.getMonth() !== view.m) { view.y = d.getFullYear(); view.m = d.getMonth(); renderFilters(); }   // deň z ďalšieho mesiaca prepne zobrazený mesiac
      renderDays(); renderSessions();
    });

    const dlg = $('#bookModal'), form = $('form', dlg), msg = $('#bm-msg', dlg);
    sEl.addEventListener('click', e => {
      const b = e.target.closest('button[data-key]'); if (!b) return;
      const s = sessions.find(x => x.key === b.dataset.key);
      const [hh, mm] = s.time.split(':').map(Number);
      const end = new Date(2000, 0, 1, hh, mm + s.cls.duration);
      const hhmm = d => `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
      const dayName = DAY_NAMES[s.d.getDay()];
      $('#bm-s-class').textContent = s.cls.name;
      $('#bm-dot').style.background = s.cls.color;
      $('#bm-s-date').textContent = `${dayName[0].toUpperCase()}${dayName.slice(1)} ${s.d.getDate()}. ${s.d.getMonth() + 1}. ${s.d.getFullYear()}`;
      $('#bm-s-time').textContent = `${s.time} – ${hhmm(end)}`;
      $('#bm-s-wait').hidden = s.taken < s.cls.capacity;          // plná lekcia = čakacia listina
      form.dataset.summary = `${s.cls.name}, ${dayName} ${s.d.getDate()}. ${s.d.getMonth() + 1}. o ${s.time}`;
      $('#bm-title').textContent = 'Rezervácia lekcie';
      $('#bm-sub').textContent = 'Skontrolujte údaje a vyplňte kontakt';
      $('#bm-err').hidden = true;
      form.reset(); form.dataset.key = s.key; msg.hidden = true; form.hidden = false; dlg.showModal();
    });
    $('.close', dlg).addEventListener('click', () => dlg.close());
    dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
    const err = $('#bm-err'), submitBtn = $('button[type=submit]', form);
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const [date, time, cls] = (form.dataset.key || '').split('|');
      const data = new FormData(form);
      data.set('date', date); data.set('time', time); data.set('class', cls);
      submitBtn.disabled = true; err.hidden = true;
      try {
        const r = await fetch('api/reserve.php', { method: 'POST', body: data });
        const j = await r.json();
        if (!j.ok) throw new Error(j.message);
        $('#bm-title').textContent = 'Ďakujeme!';
        $('#bm-sub').textContent = 'Vašu žiadosť sme prijali';
        msg.className = 'done';
        msg.innerHTML = `<span class="done-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span>
          <h4>Rezerváciu sme prijali</h4>
          <p><strong></strong></p>
          <p>Potvrdenie o prijatí žiadosti sme poslali na váš e-mail.</p>
          <button type="button" class="btn btn-primary btn-sm" data-done>Zavrieť</button>`;
        $('strong', msg).textContent = form.dataset.summary || '';
        $('[data-done]', msg).addEventListener('click', () => dlg.close());
        msg.hidden = false; form.hidden = true;
      } catch (ex) {
        err.textContent = ex.message && ex.message !== 'Failed to fetch' ? ex.message : 'Rezerváciu sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.';
        err.hidden = false;
      }
      submitBtn.disabled = false;
    });
  }

  /* Gallery */
  const tabs = $$('.tabs button');
  tabs.forEach(t => t.addEventListener('click', () => {
    tabs.forEach(x => x.setAttribute('aria-pressed', x === t));
    $$('.masonry figure').forEach(f => { f.hidden = t.dataset.cat !== 'all' && f.dataset.cat !== t.dataset.cat; });
  }));
  /* Prehliadač fotiek a videí (galéria): šípky, potiahnutie prstom, klávesy.
     Z fotky sa listuje medzi (vyfiltrovanými) fotkami, z videa medzi videami. */
  const lb = $('.lightbox');
  if (lb) {
    const img = $('img', lb), vid = $('video', lb), count = $('.lb-count', lb);
    let list = [], cur = 0;
    const info = el => el.hasAttribute('data-lb-video')
      ? { video: true, src: el.dataset.lbVideo, poster: $('video', el)?.getAttribute('poster') || '' }
      : { video: false, src: $('img', el).dataset.full || $('img', el).currentSrc || $('img', el).src, alt: $('img', el).alt };
    const stopVideo = () => { vid.pause(); vid.removeAttribute('src'); vid.load(); };
    const show = (n, dir = 0) => {
      cur = (n + list.length) % list.length;
      const it = info(list[cur]), el = it.video ? vid : img;
      [img, vid].forEach(x => x.classList.remove('swap-l', 'swap-r'));
      void el.offsetWidth;                                   // reštart animácie
      if (it.video) {
        img.hidden = true; vid.hidden = false;
        vid.poster = it.poster; vid.src = it.src; vid.play().catch(() => {});
      } else {
        stopVideo(); vid.hidden = true; img.hidden = false;
        img.src = it.src; img.alt = it.alt;
      }
      if (dir && !reduce) el.classList.add(dir > 0 ? 'swap-r' : 'swap-l');
      count.textContent = `${cur + 1} / ${list.length}`;
      count.dataset.kind = it.video ? 'video' : 'foto';
      if (!it.video) [1, -1].forEach(d => { const o = info(list[(cur + d + list.length) % list.length]); if (!o.video) new Image().src = o.src; });
    };
    const open = el => {
      list = el.hasAttribute('data-lb-video') ? $$('[data-lb-video]') : $$('.masonry figure').filter(x => !x.hidden);
      $$('.video-card video').forEach(v => { v.pause(); v.closest('.video-card').classList.remove('playing'); });
      lb.classList.toggle('single', list.length < 2);
      show(list.indexOf(el));
      lb.classList.add('open'); document.body.style.overflow = 'hidden';
    };
    const close = () => { lb.classList.remove('open'); document.body.style.overflow = ''; stopVideo(); };
    const bind = el => {
      el.addEventListener('click', e => { if (!e.target.closest('.sound')) open(el); });
      el.addEventListener('keydown', e => { if ((e.key === 'Enter' || e.key === ' ') && e.target === el) { e.preventDefault(); open(el); } });
    };
    $$('.masonry figure, [data-lb-video]').forEach(bind);
    $('.lb-prev', lb).addEventListener('click', e => { e.stopPropagation(); show(cur - 1, -1); });
    $('.lb-next', lb).addEventListener('click', e => { e.stopPropagation(); show(cur + 1, 1); });
    $('.lb-close', lb).addEventListener('click', close);
    lb.addEventListener('click', e => { if (e.target === lb || e.target.classList.contains('lb-stage')) close(); });
    addEventListener('keydown', e => {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape') close();
      else if (e.target === vid) return;                     // šípky na videu ovládajú prehrávanie
      else if (e.key === 'ArrowRight') show(cur + 1, 1);
      else if (e.key === 'ArrowLeft') show(cur - 1, -1);
    });
    let sx = null;
    lb.addEventListener('pointerdown', e => { if (e.target === img || e.target === lb || e.target.classList.contains('lb-stage')) sx = e.clientX; });
    lb.addEventListener('pointerup', e => {
      if (sx === null) return;
      const dx = e.clientX - sx; sx = null;
      if (Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1);
    });
    lb.addEventListener('pointercancel', () => { sx = null; });
  }

  /* Objednávka darčekovej poukážky */
  const vf = $('#voucherForm');
  if (vf) {
    const typeSel = $('#v-type'), amountWrap = $('#v-amount-wrap'), countWrap = $('#v-count-wrap');
    const amount = $('#v-amount'), count = $('#v-count'), err = $('#v-err'), btn = $('button[type=submit]', vf);
    const sync = () => {                                        // suma len pri „Poukážka na sumu", inak počet lekcií
      const sum = typeSel.value === 'sum';
      amountWrap.hidden = !sum; countWrap.hidden = sum;
      amount.required = sum; amount.disabled = !sum; count.disabled = sum;
    };
    typeSel.addEventListener('change', sync); sync();
    vf.addEventListener('submit', async e => {
      e.preventDefault();
      btn.disabled = true; err.hidden = true;
      try {
        const r = await fetch('api/voucher.php', { method: 'POST', body: new FormData(vf) });
        const j = await r.json();
        if (!j.ok) throw new Error(j.message);
        $('#v-summary').textContent = j.summary || '';
        vf.hidden = true; $('#v-done').hidden = false;
        $('#voucherPanel').scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
      } catch (ex) {
        err.textContent = ex.message && ex.message !== 'Failed to fetch' ? ex.message : 'Objednávku sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.';
        err.hidden = false;
      }
      btn.disabled = false;
    });
  }

  /* Contact form */
  const cf = $('#contactForm');
  if (cf) cf.addEventListener('submit', async e => {
    e.preventDefault();
    const m = $('.form-msg', cf), btn = $('button[type=submit]', cf);
    btn.disabled = true; m.hidden = true;
    try {
      const r = await fetch('api/contact.php', { method: 'POST', body: new FormData(cf) });
      const j = await r.json();
      m.className = 'form-msg ' + (j.ok ? 'ok' : 'err'); m.textContent = j.message; m.hidden = false;
      if (j.ok) cf.reset();
    } catch { m.className = 'form-msg err'; m.textContent = 'Správu sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.'; m.hidden = false; }
    btn.disabled = false;
  });
})();
