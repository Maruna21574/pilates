/* Interaktívny 3D model reformeru (Three.js, vytvorený priamo v kóde – bez externých modelov).
   Otáčanie ťahom, približovanie tlačidlami/štipnutím, číslované body s vysvetlením a ukážka pohybu plošiny.
   Spúšťa sa z main.js, keď je sekcia blízko okna. Vyžaduje globálne THREE (assets/js/vendor/three.min.js). */
(() => {
  'use strict';
  const T = window.THREE;
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const PI = Math.PI, clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  const lin = hex => new T.Color(hex).convertSRGBToLinear();     // farby zadávame v sRGB, výstup renderera je sRGB

  window.OPReformer = function (stage) {
    const canvas = $('.r3d-canvas', stage), pinsBox = $('.r3d-pins', stage), loading = $('.r3d-loading', stage);
    function $(s, r = document) { return r.querySelector(s); }
    const fail = () => { stage.classList.add('no-3d'); if (loading) loading.hidden = true; };

    let renderer;
    try {
      renderer = new T.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
    } catch (e) { return fail(); }
    if (!renderer.getContext()) return fail();
    const small = matchMedia('(max-width: 720px)').matches;
    renderer.setPixelRatio(Math.min(devicePixelRatio || 1, small ? 1.6 : 2));
    renderer.outputEncoding = T.sRGBEncoding;
    renderer.toneMapping = T.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 0.82;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = T.PCFSoftShadowMap;

    const scene = new T.Scene();
    const camera = new T.PerspectiveCamera(30, 1, 0.1, 40);

    /* ---------- prostredie (štúdiové svetlo pre odlesky kovu a čalúnenia) ---------- */
    (() => {
      const env = new T.Scene();
      env.add(new T.Mesh(new T.BoxGeometry(14, 8, 14), new T.MeshBasicMaterial({ color: new T.Color(0.62, 0.72, 0.71), side: T.BackSide })));
      const panel = (w, h, x, y, z, i) => {
        const m = new T.Mesh(new T.PlaneGeometry(w, h), new T.MeshBasicMaterial({ color: new T.Color(i, i, i * 0.97), side: T.DoubleSide }));
        m.position.set(x, y, z); m.lookAt(0, 0, 0); env.add(m);
      };
      panel(6, 4, 0, 3.6, 0.5, 9);       // veľký horný softbox
      panel(1.6, 4, -4.6, 1.6, 2, 5);    // pásy zboku
      panel(1.6, 4, 4.6, 1.4, -1.5, 4);
      panel(5, 1.2, 0, 1.0, 5.5, 2.5);   // jemné svetlo spredu
      const pm = new T.PMREMGenerator(renderer);
      scene.environment = pm.fromScene(env, 0.04).texture;
      pm.dispose();
    })();

    const key = new T.DirectionalLight(0xfff4e6, 0.95);
    key.position.set(3.2, 5, 3.4); key.castShadow = true;
    key.shadow.mapSize.set(small ? 1024 : 2048, small ? 1024 : 2048);
    Object.assign(key.shadow.camera, { left: -2.6, right: 2.6, top: 2.6, bottom: -2.6, near: 0.5, far: 14 });
    key.shadow.bias = -0.0006; key.shadow.radius = 5;
    scene.add(key, new T.HemisphereLight(0xffffff, lin(0x9db8b5), 0.22));
    const floor = new T.Mesh(new T.PlaneGeometry(14, 14), new T.ShadowMaterial({ opacity: 0.2 }));
    floor.rotation.x = -PI / 2; floor.receiveShadow = true; scene.add(floor);

    /* ---------- materiály ---------- */
    const mat = (hex, o = {}) => new T.MeshStandardMaterial(Object.assign({ color: lin(hex), roughness: 0.6, metalness: 0 }, o));
    const M = {
      wood: () => mat(0xc9995f, { roughness: 0.5 }),
      leather: () => mat(0x1b1d1f, { roughness: 0.42 }),
      steel: () => mat(0xc3cbcd, { roughness: 0.24, metalness: 1 }),
      dark: () => mat(0x23272a, { roughness: 0.4, metalness: 0.85 }),
      rubber: () => mat(0x151617, { roughness: 0.85 }),
      rope: () => mat(0xeadfca, { roughness: 0.92 }),
      teal: () => mat(0x4a7c77, { roughness: 0.45 }),
    };
    const parts = {};                                   // id -> { mats:[...] } pre zvýraznenie
    const reg = (id, ...ms) => { (parts[id] = parts[id] || { mats: [] }).mats.push(...ms); return ms[0]; };

    /* ---------- pomocné tvary ---------- */
    const add = (parent, geo, material, x = 0, y = 0, z = 0) => {
      const m = new T.Mesh(geo, material); m.position.set(x, y, z); m.castShadow = true; m.receiveShadow = true; parent.add(m); return m;
    };
    const box = (p, w, h, d, material, x, y, z) => add(p, new T.BoxGeometry(w, h, d), material, x, y, z);
    const cyl = (p, r, len, material, x, y, z, axis = 'y', seg = 24) => {
      const m = add(p, new T.CylinderGeometry(r, r, len, seg), material, x, y, z);
      if (axis === 'z') m.rotation.x = PI / 2; else if (axis === 'x') m.rotation.z = PI / 2;
      return m;
    };
    /** zaoblený čalúnený blok so skosenými hranami; spodok na y=0 */
    const pad = (p, w, h, d, r, material, x, y, z) => {
      const b = Math.min(0.018, h / 3), rr = Math.max(0.004, r - b), w2 = w - 2 * b, d2 = d - 2 * b;
      const s = new T.Shape();
      s.moveTo(-w2 / 2 + rr, -d2 / 2); s.lineTo(w2 / 2 - rr, -d2 / 2); s.quadraticCurveTo(w2 / 2, -d2 / 2, w2 / 2, -d2 / 2 + rr);
      s.lineTo(w2 / 2, d2 / 2 - rr); s.quadraticCurveTo(w2 / 2, d2 / 2, w2 / 2 - rr, d2 / 2);
      s.lineTo(-w2 / 2 + rr, d2 / 2); s.quadraticCurveTo(-w2 / 2, d2 / 2, -w2 / 2, d2 / 2 - rr);
      s.lineTo(-w2 / 2, -d2 / 2 + rr); s.quadraticCurveTo(-w2 / 2, -d2 / 2, -w2 / 2 + rr, -d2 / 2);
      const g = new T.ExtrudeGeometry(s, { depth: h - 2 * b, bevelEnabled: true, bevelThickness: b, bevelSize: b, bevelSegments: 5, curveSegments: 10 });
      g.rotateX(-PI / 2); g.translate(0, b, 0);
      return add(p, g, material, x, y, z);
    };
    const between = (p, a, b, r, material) => {          // valec medzi dvoma bodmi
      const m = add(p, new T.CylinderGeometry(r, r, 1, 10), material);
      m.userData = { a, b, r }; setSeg(m, a, b); return m;
    };
    const dir = new T.Vector3(), up = new T.Vector3(0, 1, 0);
    function setSeg(m, a, b) {
      dir.subVectors(b, a); const len = dir.length();
      m.position.copy(a).addScaledVector(dir, 0.5);
      m.quaternion.setFromUnitVectors(up, dir.normalize()); m.scale.set(1, len, 1);
    }

    /* ---------- model ---------- */
    const model = new T.Group(); scene.add(model);
    const L = 2.3, frameMat = reg('frame', M.wood());
    const tracksMat = reg('frame', mat(0xb9c2c4, { roughness: 0.3, metalness: 1 }));
    const legMat = frameMat;

    // rám a koľajnice
    for (const z of [-0.29, 0.29]) {
      box(model, L, 0.07, 0.06, frameMat, 0, 0.30, z);
      box(model, L - 0.4, 0.012, 0.034, tracksMat, -0.02, 0.341, z);
    }
    for (const x of [-1.12, 1.12]) box(model, 0.07, 0.07, 0.64, frameMat, x, 0.30, 0);
    box(model, 0.06, 0.06, 0.56, frameMat, 0, 0.30, 0);
    for (const x of [-1.10, 0, 1.10]) for (const z of [-0.29, 0.29]) {
      box(model, 0.07, 0.30, 0.07, legMat, x, 0.15, z);
      cyl(model, 0.042, 0.012, reg('frame', M.rubber()), x, 0.006, z, 'y', 20);
    }

    // pohyblivá plošina
    const carriage = new T.Group(); model.add(carriage);
    const cBase = reg('carriage', M.wood()), cPad = reg('carriage', M.leather());
    box(carriage, 1.05, 0.035, 0.60, cBase, 0, 0.392, 0);
    pad(carriage, 1.03, 0.08, 0.54, 0.06, cPad, 0, 0.4095, 0);
    const wheelMat = reg('carriage', M.dark());
    for (const x of [-0.40, 0.40]) for (const z of [-0.29, 0.29]) cyl(carriage, 0.022, 0.024, wheelMat, x, 0.369, z, 'z', 18);

    // opierky ramien a hlavy
    const shMat = reg('shoulder', M.leather()), hdMat = reg('head', M.leather());
    for (const z of [-0.19, 0.19]) pad(carriage, 0.075, 0.105, 0.115, 0.03, shMat, -0.30, 0.4895, z);
    pad(carriage, 0.22, 0.05, 0.27, 0.05, hdMat, -0.42, 0.4895, 0);

    // háčiky pružín (farebné značky podľa sily pružín) a úchyty lanka
    const hookMat = reg('springs', M.steel());
    box(carriage, 0.035, 0.03, 0.34, hookMat, 0.525, 0.372, 0);
    const SPRING_Z = [-0.12, -0.04, 0.04, 0.12], TAG = [0xd2ab88, 0x4a7c77, 0x92b0af, 0xf3f7f6];
    SPRING_Z.forEach((z, i) => cyl(carriage, 0.013, 0.034, reg('springs', mat(TAG[i], { roughness: 0.5 })), 0.552, 0.345, z, 'x', 16));
    for (const z of [-0.15, 0.15]) cyl(carriage, 0.008, 0.05, reg('ropes', M.steel()), -0.52, 0.372, z, 'x', 12);

    // pružiny
    const BAR_X = 1.10, SPRING_Y = 0.322;
    cyl(model, 0.011, 0.56, reg('springs', M.steel()), BAR_X, SPRING_Y, 0, 'z', 16);
    const helix = (() => {
      const pts = [], turns = 26, per = 12, R = 0.021;
      for (let i = 0; i <= turns * per; i++) { const t = i / (turns * per), a = t * turns * 2 * PI; pts.push(new T.Vector3(-t, Math.cos(a) * R, Math.sin(a) * R)); }
      return new T.TubeGeometry(new T.CatmullRomCurve3(pts), turns * per * 2, 0.0058, 8, false);
    })();
    const springMat = reg('springs', mat(0xaab4b7, { roughness: 0.3, metalness: 1 }));
    const springs = SPRING_Z.map(z => { const m = add(model, helix, springMat, BAR_X, SPRING_Y, z); return m; });

    // opierka nôh
    const fbMat = reg('footbar', M.steel()), fbBase = reg('footbar', M.rubber()), fbGrip = reg('footbar', M.rubber());
    for (const z of [-0.2, 0.2]) {
      box(model, 0.075, 0.05, 0.075, fbBase, 1.03, 0.36, z);
      cyl(model, 0.0165, 0.26, fbMat, 1.03, 0.51, z, 'y', 20);
      add(model, new T.SphereGeometry(0.024, 20, 14), fbMat, 1.03, 0.64, z);
    }
    cyl(model, 0.018, 0.40, fbMat, 1.03, 0.64, 0, 'z', 20);
    for (const z of [-0.12, 0.12]) cyl(model, 0.024, 0.17, fbGrip, 1.03, 0.64, z, 'z', 20);

    // zadná časť: stĺpiky, kladky a popruhy
    const postMat = reg('ropes', M.wood()), pulMat = reg('ropes', M.dark()), ropeMat = reg('ropes', M.rope()), handleMat = reg('ropes', M.teal());
    for (const z of [-0.27, 0.27]) box(model, 0.052, 0.42, 0.052, postMat, -1.08, 0.545, z);
    box(model, 0.052, 0.05, 0.60, postMat, -1.08, 0.775, 0);
    const PULX = -1.045, PULY = 0.69, PR = 0.032;
    const ropeMeshes = [];                                 // pohyblivé úseky lanka (plošina -> kladka)
    for (const z of [-0.15, 0.15]) {
      box(model, 0.02, 0.07, 0.02, pulMat, -1.08, 0.735, z);
      cyl(model, PR, 0.022, pulMat, PULX, PULY, z, 'z', 24);
      // lanko okolo kladky (oblúk) a visiaca slučka s rúčkou
      const arc = []; for (let a = -PI / 2; a <= PI / 2 + 0.001; a += PI / 16) arc.push(new T.Vector3(PULX - Math.cos(a) * (PR + 0.004), PULY + Math.sin(a) * (PR + 0.004), z));
      add(model, new T.TubeGeometry(new T.CatmullRomCurve3(arc), 24, 0.0072, 8, false), ropeMat);
      const top = new T.Vector3(PULX, PULY + PR + 0.004, z), H = new T.Vector3(-0.60, 0.585, z);
      const hang = new T.CubicBezierCurve3(top, new T.Vector3(-0.92, 0.77, z), new T.Vector3(-0.72, 0.79, z), H);
      add(model, new T.TubeGeometry(hang, 36, 0.0072, 8, false), ropeMat);
      const ring = add(model, new T.TorusGeometry(0.034, 0.0085, 12, 28), handleMat, H.x, H.y - 0.034, z); ring.rotation.y = PI / 2;
      // pohyblivý úsek lanka: plošina -> spodok kladky
      const bottom = new T.Vector3(PULX, PULY - PR - 0.004, z);
      ropeMeshes.push({ m: between(model, new T.Vector3(0, 0, 0), bottom, 0.0072, ropeMat), bottom, z });
    }

    /* ---------- pohyb plošiny ---------- */
    const REST = 0.34, FAR = -0.12;
    let cx = REST, playing = false, playT = 0;
    function poseCarriage() {
      carriage.position.x = cx;
      springs.forEach(s => { s.scale.x = Math.max(0.04, BAR_X - (cx + 0.568)); });
      ropeMeshes.forEach(r => setSeg(r.m, new T.Vector3(cx - 0.52, 0.372, r.z), r.bottom));
    }
    poseCarriage();

    /* ---------- body s vysvetlením ---------- */
    const list = [...stage.parentElement.querySelectorAll('[data-part]')];
    const worldOf = {                                     // kotvy bodov v priestore
      frame:    () => new T.Vector3(0.45, 0.30, 0.33),
      carriage: () => new T.Vector3(cx + 0.1, 0.5, 0),
      springs:  () => new T.Vector3((BAR_X + cx + 0.55) / 2, 0.325, 0.12),
      footbar:  () => new T.Vector3(1.03, 0.665, 0),
      shoulder: () => new T.Vector3(cx - 0.30, 0.6, 0.23),
      head:     () => new T.Vector3(cx - 0.56, 0.55, 0),
      ropes:    () => new T.Vector3(-1.045, 0.79, -0.15),
    };
    const focusOf = id => { const v = worldOf[id](); v.y = Math.min(v.y, 0.47); return v; };   // bod, na ktorý kamera mieri (nie príliš vysoko, aby model nevypadol zo záberu)
    const VIEW = {                                       // [azimut, sklon, vzdialenosť]
      frame: [0.45, 1.3, 3.3], carriage: [0.75, 1.1, 2.7], springs: [1.15, 1.2, 2.0], footbar: [0.95, 1.2, 2.1],
      shoulder: [-0.75, 1.05, 2.5], head: [-0.95, 1.12, 2.8], ropes: [-0.5, 1.2, 2.7],
    };
    const pins = list.map((li, i) => {
      const id = li.dataset.part, b = document.createElement('button');
      b.type = 'button'; b.className = 'r3d-pin'; b.innerHTML = '<span class="r3d-pin-i">' + (i + 1) + '</span>'; b.tabIndex = -1;
      b.setAttribute('aria-label', li.querySelector('.r3d-name').textContent);
      // výber pri stlačení (nie až pri pustení): body sa počas letu kamery hýbu a samotný click by ľahko minul
      b.addEventListener('pointerdown', e => { e.preventDefault(); e.stopPropagation(); select(id); });
      b.addEventListener('click', e => e.preventDefault());
      pinsBox.appendChild(b); return { id, b, v: new T.Vector3() };
    });

    /* ---------- kamera (vlastné otáčanie, bez knižnice) ---------- */
    const home = { th: 0.85, ph: 1.12, r: 4.05, t: new T.Vector3(0.02, 0.3, 0) };
    const cam = { th: home.th, ph: home.ph, r: home.r, t: home.t.clone() };
    let tween = null, auto = !reduce, lastInput = 0, aspect = 1.4, hover = false;
    stage.addEventListener('pointerenter', () => { hover = true; });      // kým je kurzor/prst nad modelom, neotáča sa (body sa nehýbu)
    stage.addEventListener('pointerleave', () => { hover = false; });
    const fit = () => (aspect < 1.3 ? Math.min(1.6, 1.3 / aspect * 0.92) : 1);   // na úzkom displeji kameru oddiali
    function applyCam() {
      const r = cam.r * fit();
      camera.position.set(cam.t.x + r * Math.sin(cam.ph) * Math.sin(cam.th), cam.t.y + r * Math.cos(cam.ph), cam.t.z + r * Math.sin(cam.ph) * Math.cos(cam.th));
      camera.lookAt(cam.t);
    }
    const ease = t => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
    function flyTo(th, ph, r, t, ms = 950) {
      const d = ((th - cam.th + PI) % (2 * PI) + 2 * PI) % (2 * PI) - PI;      // najkratšia cesta po kružnici
      tween = { t0: performance.now(), ms: reduce ? 1 : ms, th0: cam.th, th1: cam.th + d, ph0: cam.ph, ph1: ph, r0: cam.r, r1: r, a: cam.t.clone(), b: t.clone() };
      auto = false;
    }
    function stepTween(now) {
      if (!tween) return;
      const k = clamp((now - tween.t0) / tween.ms, 0, 1), e = ease(k);
      cam.th = tween.th0 + (tween.th1 - tween.th0) * e; cam.ph = tween.ph0 + (tween.ph1 - tween.ph0) * e; cam.r = tween.r0 + (tween.r1 - tween.r0) * e;
      cam.t.lerpVectors(tween.a, tween.b, e);
      if (k >= 1) { tween = null; placeCard(); }       // po doletení kamery znovu zvoľ najvhodnejšie miesto pre kartu
    }
    const interact = () => { tween = null; auto = false; lastInput = performance.now(); stage.classList.add('touched'); };

    const ptrs = new Map(); let pinch = 0;
    canvas.addEventListener('pointerdown', e => { canvas.setPointerCapture(e.pointerId); ptrs.set(e.pointerId, { x: e.clientX, y: e.clientY }); interact(); canvas.classList.add('drag'); });
    canvas.addEventListener('pointermove', e => {
      const p = ptrs.get(e.pointerId); if (!p) return;
      const dx = e.clientX - p.x, dy = e.clientY - p.y; p.x = e.clientX; p.y = e.clientY;
      if (ptrs.size === 1) { cam.th -= dx * 0.0072; cam.ph = clamp(cam.ph - dy * 0.0062, 0.28, 1.52); interact(); }
      else if (ptrs.size === 2) {
        const [a, b] = [...ptrs.values()], d = Math.hypot(a.x - b.x, a.y - b.y);
        if (pinch) cam.r = clamp(cam.r * (pinch / d), 1.3, 6.5); pinch = d; interact();
      }
    });
    const end = e => { ptrs.delete(e.pointerId); pinch = 0; if (!ptrs.size) canvas.classList.remove('drag'); };
    canvas.addEventListener('pointerup', end); canvas.addEventListener('pointercancel', end);
    canvas.addEventListener('wheel', e => { if (!e.ctrlKey) return; e.preventDefault(); interact(); cam.r = clamp(cam.r * (1 + e.deltaY * 0.0015), 1.3, 6.5); }, { passive: false });
    canvas.addEventListener('dblclick', () => reset());
    canvas.addEventListener('keydown', e => {
      const k = { ArrowLeft: [0.12, 0], ArrowRight: [-0.12, 0], ArrowUp: [0, 0.1], ArrowDown: [0, -0.1] }[e.key];
      if (k) { e.preventDefault(); interact(); cam.th += k[0]; cam.ph = clamp(cam.ph + k[1], 0.28, 1.52); }
      else if (e.key === '+' || e.key === '=') { interact(); cam.r = clamp(cam.r * 0.88, 1.3, 6.5); }
      else if (e.key === '-') { interact(); cam.r = clamp(cam.r * 1.12, 1.3, 6.5); }
    });

    /* ---------- ovládanie a výber ---------- */
    let active = null;
    const card = $('.r3d-card', stage), cardT = $('.r3d-card-t', stage), cardP = $('.r3d-card-p', stage);
    function select(id) {
      active = id;
      const li = list.find(x => x.dataset.part === id);
      if (card && li) { cardT.textContent = li.querySelector('.r3d-name').textContent; cardP.textContent = li.querySelector('.r3d-text').textContent; card.hidden = false; stage.classList.add('touched'); placeCard(); }
      list.forEach(li => { const on = li.dataset.part === id; li.classList.toggle('active', on); li.querySelector('.r3d-head').setAttribute('aria-expanded', on); });
      pins.forEach(p => p.b.classList.toggle('active', p.id === id));
      const v = VIEW[id]; if (v) flyTo(v[0], v[1], v[2], focusOf(id));
      lastInput = performance.now();
    }
    function reset() { active = null; cardRect = null; if (card) card.hidden = true; list.forEach(li => { li.classList.remove('active'); li.querySelector('.r3d-head').setAttribute('aria-expanded', 'false'); }); pins.forEach(p => p.b.classList.remove('active')); flyTo(home.th, home.ph, home.r, home.t); }
    list.forEach(li => li.querySelector('.r3d-head').addEventListener('click', () => (active === li.dataset.part ? reset() : select(li.dataset.part))));
    $('.r3d-card-x', stage)?.addEventListener('click', reset);
    const playBtn = $('[data-r3d-play]', stage), zIn = $('[data-r3d-zoom="in"]', stage), zOut = $('[data-r3d-zoom="out"]', stage), rst = $('[data-r3d-reset]', stage);
    playBtn?.addEventListener('click', () => { playing = !playing; playT = 0; playBtn.setAttribute('aria-pressed', playing); playBtn.querySelector('span').textContent = playing ? 'Zastaviť pohyb' : 'Ukázať pohyb'; });
    zIn?.addEventListener('click', () => { interact(); cam.r = clamp(cam.r * 0.82, 1.3, 6.5); });
    zOut?.addEventListener('click', () => { interact(); cam.r = clamp(cam.r * 1.22, 1.3, 6.5); });
    rst?.addEventListener('click', reset);

    /* ---------- vykresľovanie ---------- */
    const hl = new T.Color(lin(0x4a7c77));
    const proj = new T.Vector3(), toCam = new T.Vector3(), toPt = new T.Vector3();
    function updatePins() {
      const w = stage.clientWidth, h = stage.clientHeight;
      pins.forEach(p => {
        p.v.copy(worldOf[p.id]());
        toCam.subVectors(camera.position, cam.t); toPt.subVectors(p.v, cam.t);
        const behind = toCam.dot(toPt) < -0.04;                                     // bod na odvrátenej strane modelu
        proj.copy(p.v).project(camera);
        p.b.style.transform = `translate(${((proj.x + 1) / 2 * w).toFixed(1)}px, ${((1 - proj.y) / 2 * h).toFixed(1)}px) translate(-50%, -50%)`;
        p.b.classList.toggle('behind', behind);
        const out = proj.z > 1 || proj.x < -0.97 || proj.x > 0.97 || proj.y < -0.97 || proj.y > 0.97;   // mimo plátna
        p.sx = (proj.x + 1) / 2 * w; p.sy = (1 - proj.y) / 2 * h; p.out = out;
        // bod, ktorý by ostal pod kartou s popisom, sa skryje (vybraný bod nikdy), aby sa nič neprekrývalo
        const underCard = cardRect && p.id !== active && p.sx > cardRect.l && p.sx < cardRect.r && p.sy > cardRect.t && p.sy < cardRect.b;
        p.b.style.visibility = (out || underCard) ? 'hidden' : 'visible';
      });
    }
    /* Karta s popisom si vyberie z 4 rohov to miesto, kde zakrýva najmenej bodov (vybraný bod nesmie zakryť vôbec). */
    let cardRect = null;
    function placeCard() {
      if (!card || card.hidden) { cardRect = null; return; }
      const sw = stage.clientWidth, sh = stage.clientHeight, m = 16, bot = matchMedia('(max-width: 520px)').matches ? 70 : 75;
      const cw = card.offsetWidth, ch = card.offsetHeight;
      const rects = { bl: { l: m, t: sh - bot - ch }, br: { l: sw - m - cw, t: sh - bot - ch }, tl: { l: m, t: m }, tr: { l: sw - m - cw, t: m } };
      let best = 'bl', bestScore = Infinity;
      ['bl', 'br', 'tl', 'tr'].forEach((k, i) => {
        const r = rects[k]; let s = i * 0.01;
        pins.forEach(p => {
          if (p.out || p.sx === undefined) return;
          if (p.sx > r.l - 16 && p.sx < r.l + cw + 16 && p.sy > r.t - 16 && p.sy < r.t + ch + 16) s += p.id === active ? 10 : 1;
        });
        if (s < bestScore) { bestScore = s; best = k; }
      });
      card.classList.remove('pos-bl', 'pos-br', 'pos-tl', 'pos-tr'); card.classList.add('pos-' + best);
      const r = rects[best]; cardRect = { l: r.l - 14, t: r.t - 14, r: r.l + cw + 14, b: r.t + ch + 14 };
    }
    function glow(dt) {
      for (const id in parts) {
        const on = id === active, k = 1 - Math.exp(-dt * 7);
        parts[id].mats.forEach(m => { const t = on ? 0.38 : 0; m.userData.g = (m.userData.g || 0) + (t - (m.userData.g || 0)) * k; m.emissive.copy(hl).multiplyScalar(m.userData.g); });
      }
    }
    let raf = 0, visible = true, last = performance.now();
    function frame(now) {
      raf = 0; if (!visible || document.hidden) return;
      const dt = Math.min(0.05, (now - last) / 1000); last = now;
      if (auto && !ptrs.size && !hover) cam.th += dt * 0.12;
      stepTween(now);
      // plošina: plynulý chod pri ukážke, inak pokojný návrat
      if (playing) { playT += dt; const s = 0.5 - 0.5 * Math.cos(playT * 1.15); cx = REST + (FAR - REST) * s; }
      else cx += (REST - cx) * (1 - Math.exp(-dt * 5));
      poseCarriage();
      if (active) { cam.t.lerp(focusOf(active), 1 - Math.exp(-dt * 3)); }   // kamera sleduje vybraný diel, aj keď sa hýbe
      applyCam(); glow(dt);
      renderer.render(scene, camera); updatePins();
      raf = requestAnimationFrame(frame);
    }
    const kick = () => { if (!raf) { last = performance.now(); raf = requestAnimationFrame(frame); } };
    new IntersectionObserver(([e]) => { visible = e.isIntersecting; if (visible) kick(); }, { threshold: 0.05 }).observe(stage);
    document.addEventListener('visibilitychange', kick);

    function resize() {
      const w = stage.clientWidth, h = stage.clientHeight; if (!w || !h) return;
      renderer.setSize(w, h, false); aspect = w / h; camera.aspect = aspect; camera.updateProjectionMatrix();
      placeCard();
    }
    new ResizeObserver(resize).observe(stage); resize();
    applyCam(); renderer.render(scene, camera);
    if (loading) loading.hidden = true;
    stage.classList.add('ready');
    kick();
  };
})();
