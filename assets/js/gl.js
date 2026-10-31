/* 3D scéna: jemné krúžky a gule z motívu loga, ktoré plávajú, reagujú na myš a scroll.
   Vyžaduje globálne THREE (assets/js/vendor/three.min.js). Spúšťa sa z main.js. */
(() => {
  'use strict';
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const small = matchMedia('(max-width: 820px)').matches;

  // Rozloženie: nx/ny sú relatívne k šírke/výške viditeľnej plochy (-1 … 1), nz je hĺbka.
  const LAYOUTS = {
    // Len tenké priesvitné krúžky (motív loga), pomalé a jemné.
    dark: [
      { t: 'ring',  nx:  0.86, ny:  0.88, nz: -1.0, s: 1.0, c: 0xe6f0ee, v: .12 },
      { t: 'ring',  nx: -0.90, ny: -0.88, nz: -0.8, s: 1.1, c: 0xc4d6d4, v: .10 },
      { t: 'ring',  nx:  1.15, ny: -0.25, nz: -1.4, s: 1.1, c: 0xffffff, v: .12 },
    ],
    hero: [
      { t: 'ring',  nx:  0.62, ny:  0.05, nz: -0.6, s: 1.5,  c: 0xe6f0ee, v: .12 },
      { t: 'ring',  nx:  0.90, ny:  0.60, nz: -1.0, s: 0.75, c: 0xffffff, v: .16 },
      { t: 'ring',  nx:  0.38, ny: -0.70, nz: -0.4, s: 0.55, c: 0xc4d6d4, v: .14 },
      { t: 'ring',  nx:  0.66, ny:  0.05, nz:  0.4, s: 0.9,  c: 0xd2ab88, v: .10, tube: .018 },
    ],
    cta: [
      { t: 'ring',  nx: -0.88, ny: -0.2, nz: -0.8, s: 1.2, c: 0xe6f0ee, v: .12 },
      { t: 'ring',  nx:  0.88, ny:  0.3, nz: -1.0, s: 1.0, c: 0xc4d6d4, v: .10 },
    ],
  };

  window.OPGL = function (canvas, variant = 'dark') {
    const T = window.THREE;
    const parent = canvas.parentElement;
    const renderer = new T.WebGLRenderer({ canvas, alpha: true, antialias: !small });
    renderer.setPixelRatio(small ? 1 : Math.min(devicePixelRatio || 1, 1.75));
    const scene = new T.Scene();
    const camera = new T.PerspectiveCamera(38, 1, .1, 60);
    camera.position.z = 9;

    scene.add(new T.HemisphereLight(0xffffff, 0x2b504b, 1.0));
    const key = new T.DirectionalLight(0xffffff, 1.5); key.position.set(3, 5, 6); scene.add(key);
    const warm = new T.PointLight(0xd2ab88, 1.6, 40); warm.position.set(-6, -3, 5); scene.add(warm);

    const group = new T.Group(); scene.add(group);
    const items = LAYOUTS[variant].map((d, i) => {
      const geo = d.t === 'ring'
        ? new T.TorusGeometry(1, d.tube || .035, 28, 180)
        : new T.SphereGeometry(1, 48, 32);
      const mat = d.t === 'ring'
        ? new T.MeshStandardMaterial({ color: d.c, metalness: .1, roughness: .5, transparent: true, opacity: .5 })
        : new T.MeshPhysicalMaterial({ color: d.c, roughness: .18, metalness: .05, clearcoat: 1, clearcoatRoughness: .1 });
      const m = new T.Mesh(geo, mat);
      m.scale.setScalar(d.s);
      m.rotation.set(Math.random() * 3, Math.random() * 3, 0);
      group.add(m);
      return { m, d, phase: i * 1.7 };
    });

    let w = 1, h = 1, halfW = 1, halfH = 1, visible = false, raf = 0;
    const px = { x: 0, y: 0, tx: 0, ty: 0 };
    let scrollP = 0;

    const place = () => {
      const narrow = Math.min(1, w / 900);                // na mobile menšie telesá
      items.forEach(({ m, d }) => {
        m.position.set(d.nx * halfW, d.ny * halfH, d.nz * 3);
        m.scale.setScalar(d.s * (0.55 + 0.45 * narrow));
      });
    };
    const resize = () => {
      w = parent.clientWidth; h = parent.clientHeight;
      renderer.setSize(w, h, false);
      camera.aspect = w / h; camera.updateProjectionMatrix();
      halfH = Math.tan(T.MathUtils.degToRad(camera.fov / 2)) * camera.position.z;
      halfW = halfH * camera.aspect;
      place(); if (!raf) draw(0);
    };

    const clock = new T.Clock();
    const draw = () => {
      const t = clock.getElapsedTime();
      px.x += (px.tx - px.x) * .05; px.y += (px.ty - px.y) * .05;
      group.rotation.y = px.x * .22; group.rotation.x = -px.y * .14;
      group.position.y = scrollP * 1.1;
      items.forEach(({ m, d, phase }) => {
        if (!reduce) {
          m.rotation.x += d.v * .004; m.rotation.y += d.v * .006;
          m.position.y = d.ny * halfH + Math.sin(t * .6 * (0.6 + d.v) + phase) * .28;
        }
        m.rotation.z = scrollP * d.v * 2;
      });
      renderer.render(scene, camera);
    };
    const loop = () => { raf = requestAnimationFrame(loop); draw(); };

    new IntersectionObserver(([e]) => {
      visible = e.isIntersecting;
      if (visible && !reduce && !raf) loop();
      else if (!visible && raf) { cancelAnimationFrame(raf); raf = 0; }
    }, { rootMargin: '100px' }).observe(parent);

    addEventListener('pointermove', e => {
      px.tx = (e.clientX / innerWidth - .5) * 2; px.ty = (e.clientY / innerHeight - .5) * 2;
    }, { passive: true });
    const onScroll = () => {
      const r = parent.getBoundingClientRect();
      scrollP = Math.max(-1, Math.min(1, (r.top + r.height / 2 - innerHeight / 2) / innerHeight));
    };
    addEventListener('scroll', onScroll, { passive: true }); onScroll();
    new ResizeObserver(resize).observe(parent); resize();
  };
})();
