/* ============================================================
   BAAM Sneaker Marketplace – script.js
   Geteiltes Skript für ALLE Seiten.
   Jede Funktion prüft per Guard, ob die nötigen Elemente
   auf der aktuellen Seite existieren.
   ============================================================ */

const byId = (id) => document.getElementById(id);

/* ── Hamburger Menu (Mobile) ── */
const hamburger = byId('hamburger');
const mobileNav = byId('mobile-nav');

if (hamburger && mobileNav) {
  hamburger.addEventListener('click', () => {
    const open = hamburger.classList.toggle('open');
    mobileNav.classList.toggle('open');
    hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  mobileNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      hamburger.classList.remove('open');
      mobileNav.classList.remove('open');
      hamburger.setAttribute('aria-expanded', 'false');
    });
  });
}

/* ── Aktiven Navigations-Link hervorheben (anhand der aktuellen Seite) ──
   So bleibt das Header-HTML auf jeder Seite exakt gleich (kopierbar).
   Vergleich ignoriert .html und Anker (#), damit es auch mit
   "sauberen" URLs (z.B. /ueber-uns) funktioniert. */
(function highlightActiveLink() {
  function base(path) {
    let p = (path || '').split('/').pop().split('#')[0].split('?')[0];
    if (!p) p = 'index';
    return p.replace(/\.(html|php)$/, '');
  }
  const current = base(location.pathname);
  document.querySelectorAll('.main-nav a, #mobile-nav a').forEach(link => {
    const href = link.getAttribute('href') || '';
    if (href.includes('#')) return; // Anker-Links nicht markieren
    if (base(href) === current) link.classList.add('active');
  });
})();

/* ── Brand-Chips (Startseite – filtert Produkte im Grid) ── */
(function initHomeChips() {
  const chips   = document.querySelectorAll('.home-chips .brand-chip');
  const homeGrid = document.querySelector('.section-products .product-grid');
  if (!chips.length || !homeGrid) return;

  chips.forEach(chip => {
    chip.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const brand = chip.textContent.trim();
      homeGrid.querySelectorAll('.product-card').forEach(card => {
        const show = brand === 'Alle' || card.dataset.brand === brand;
        card.style.display = show ? '' : 'none';
      });
    });
  });
})();


/* ============================================================
   IMAGE-SLIDER (Sortiment)
   ============================================================ */
(function initSlider() {
  const slider = byId('hero-slider');
  if (!slider) return;

  const slidesWrap = slider.querySelector('.slides');
  const slides = slider.querySelectorAll('.slide');
  const dotsWrap = byId('slider-dots');
  let index = 0;
  let timer = null;

  slides.forEach((_, i) => {
    const dot = document.createElement('button');
    dot.className = 'dot' + (i === 0 ? ' active' : '');
    dot.type = 'button';
    dot.setAttribute('aria-label', 'Slide ' + (i + 1));
    dot.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(dot);
  });
  const dots = dotsWrap.querySelectorAll('.dot');

  function goTo(i) {
    index = (i + slides.length) % slides.length;
    slidesWrap.style.transform = `translateX(-${index * 100}%)`;
    dots.forEach((d, di) => d.classList.toggle('active', di === index));
  }
  function startAuto() { timer = setInterval(() => goTo(index + 1), 5000); }
  function stopAuto() { clearInterval(timer); }

  byId('slider-prev').addEventListener('click', () => goTo(index - 1));
  byId('slider-next').addEventListener('click', () => goTo(index + 1));
  slider.addEventListener('mouseenter', stopAuto);
  slider.addEventListener('mouseleave', startAuto);
  startAuto();
})();


/* ============================================================
   SORTIMENT: Filter, Suche, Sortierung über ZWEI Abschnitte
   (Neu von BAAM + Community Shoes)
   ============================================================ */
(function initSortiment() {
  const sidebar = document.querySelector('.filter-sidebar');
  const sections = Array.from(document.querySelectorAll('.shop-section'));
  if (!sidebar || !sections.length) return;

  const searchInput = byId('product-search');
  const searchBtn = byId('search-btn');
  const sortSelect = byId('product-sort');
  const priceMin = byId('f-price-min');
  const priceMax = byId('f-price-max');
  const resetBtn = byId('f-reset');
  const brandBoxes = sidebar.querySelectorAll('.f-brand');
  const colorBoxes = sidebar.querySelectorAll('.f-colorway');
  const ratingBtns = sidebar.querySelectorAll('.f-rating');
  let activeRating = null;

  ratingBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const wasActive = btn.classList.contains('active');
      ratingBtns.forEach(b => b.classList.remove('active'));
      if (wasActive) {
        activeRating = null;
      } else {
        btn.classList.add('active');
        activeRating = { min: +btn.dataset.min, max: +btn.dataset.max };
      }
      update();
    });
  });

  function matches(card) {
    const term = (searchInput.value || '').trim().toLowerCase();
    const brands = Array.from(brandBoxes).filter(b => b.checked).map(b => b.value);
    const colors = Array.from(colorBoxes).filter(b => b.checked).map(b => b.value);
    const min = parseFloat(priceMin.value);
    const max = parseFloat(priceMax.value);
    const price = +card.dataset.price;
    const rating = +card.dataset.rating;

    if (brands.length && !brands.includes(card.dataset.brand)) return false;
    if (colors.length && !colors.includes(card.dataset.colorway)) return false;
    if (!isNaN(min) && price < min) return false;
    if (!isNaN(max) && price > max) return false;
    if (activeRating && (rating < activeRating.min || rating > activeRating.max)) return false;
    if (term && !(card.dataset.name || '').toLowerCase().includes(term)) return false;
    return true;
  }

  function sortList(list) {
    const v = sortSelect.value;
    return list.slice().sort((a, b) => {
      if (v === 'price-asc') return a.dataset.price - b.dataset.price;
      if (v === 'price-desc') return b.dataset.price - a.dataset.price;
      if (v === 'rating-desc') return b.dataset.rating - a.dataset.rating;
      if (v === 'name-asc') return a.dataset.name.localeCompare(b.dataset.name);
      return 0;
    });
  }

  function update() {
    sections.forEach(sec => {
      const grid = sec.querySelector('.product-grid');
      const cards = Array.from(grid.querySelectorAll('.product-card'));
      const filtered = sortList(cards.filter(matches));
      filtered.forEach(c => grid.appendChild(c));
      const shown = new Set(filtered);
      cards.forEach(c => { c.style.display = shown.has(c) ? '' : 'none'; });

      const countEl = sec.querySelector('.sec-count');
      if (countEl) countEl.textContent = filtered.length;
      const empty = sec.querySelector('.sec-empty');
      if (empty) empty.hidden = filtered.length !== 0;
    });
  }

  searchInput.addEventListener('input', update);
  searchBtn.addEventListener('click', update);
  sortSelect.addEventListener('change', update);
  [priceMin, priceMax].forEach(el => el.addEventListener('input', update));
  [...brandBoxes, ...colorBoxes].forEach(cb => cb.addEventListener('change', update));
  resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    priceMin.value = '';
    priceMax.value = '';
    brandBoxes.forEach(b => { b.checked = false; });
    colorBoxes.forEach(b => { b.checked = false; });
    ratingBtns.forEach(b => b.classList.remove('active'));
    activeRating = null;
    sortSelect.value = 'price-asc';
    update();
  });

  update();
})();


/* ============================================================
   FORMULAR-VALIDIERUNG (generisch, mit JavaScript – kein HTML5)
   Verwendet für:
   • #anmelde-form  (Promotion-Seite, Tabelle "kunde")
   • #einsende-form (Verkaufen-Seite, Tabellen "einsendung" + "produkt")
   ============================================================ */
function nameRule(label) {
  return (val) => {
    if (!val) return label + ' ist erforderlich.';
    if (val.length < 2) return 'Mindestens 2 Zeichen.';
    if (!/^[A-Za-zÀ-ÖØ-öø-ÿ\s\-]+$/.test(val)) return 'Nur Buchstaben und Bindestriche erlaubt.';
    return '';
  };
}
function emailRule(val) {
  if (!val) return 'E-Mail ist erforderlich.';
  if (val.length > 100) return 'E-Mail darf maximal 100 Zeichen haben.';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)) return 'Bitte eine gültige E-Mail-Adresse eingeben.';
  return '';
}
function requiredSelect(message) {
  return (val) => (val ? '' : message);
}

function setupForm(form, rules, successId) {
  function valOf(el) { return el.type === 'checkbox' ? el.checked : el.value.trim(); }

  function showFieldState(key, msg) {
    const el = rules[key].el();
    const errEl = rules[key].err();
    if (!el || !errEl) return;
    errEl.textContent = msg;
    if (msg) { el.classList.add('error'); el.classList.remove('valid'); }
    else { el.classList.remove('error'); el.classList.add('valid'); }
  }

  Object.keys(rules).forEach(key => {
    const el = rules[key].el();
    if (!el) return;
    el.addEventListener('blur', () => showFieldState(key, rules[key].validate(valOf(el))));
    el.addEventListener('input', () => {
      if (el.classList.contains('error')) showFieldState(key, rules[key].validate(valOf(el)));
    });
    if (el.tagName === 'SELECT' || el.type === 'checkbox') {
      el.addEventListener('change', () => showFieldState(key, rules[key].validate(valOf(el))));
    }
  });

  form.addEventListener('submit', (e) => {
    let valid = true;
    let firstInvalid = null;
    Object.keys(rules).forEach(key => {
      const el = rules[key].el();
      if (!el) return;
      const msg = rules[key].validate(valOf(el));
      showFieldState(key, msg);
      if (msg) { valid = false; if (!firstInvalid) firstInvalid = el; }
    });

    // Ungültig -> Absenden verhindern und zum ersten Fehler springen.
    // Gültig  -> KEIN preventDefault: das Formular wird ganz normal per
    //            POST an die PHP-Seite gesendet, die serverseitig prüft,
    //            speichert und per Redirect (?ok=1) die Erfolgsmeldung zeigt.
    if (!valid) {
      e.preventDefault();
      if (firstInvalid && typeof firstInvalid.focus === 'function') firstInvalid.focus();
    }
  });
}

/* Anmeldeformular (Promotion-Seite) */
const anmeldeForm = byId('anmelde-form');
if (anmeldeForm) {
  setupForm(anmeldeForm, {
    vorname:      { el: () => byId('vorname'),      err: () => byId('err-vorname'),      validate: nameRule('Vorname') },
    nachname:     { el: () => byId('nachname'),     err: () => byId('err-nachname'),     validate: nameRule('Nachname') },
    email:        { el: () => byId('email'),        err: () => byId('err-email'),        validate: emailRule },
    schuhgroesse: { el: () => byId('schuhgroesse'), err: () => byId('err-schuhgroesse'), validate: requiredSelect('Bitte eine Schuhgrösse auswählen.') },
    agb:          { el: () => byId('agb'),          err: () => byId('err-agb'),          validate: () => byId('agb').checked ? '' : 'Bitte akzeptiere die AGB.' }
  }, 'form-success');
}

/* Einsende-Formular (Verkaufen-Seite) */
const einsendeForm = byId('einsende-form');
if (einsendeForm) {
  setupForm(einsendeForm, {
    'ev-vorname':  { el: () => byId('ev-vorname'),  err: () => byId('err-ev-vorname'),  validate: nameRule('Vorname') },
    'ev-nachname': { el: () => byId('ev-nachname'), err: () => byId('err-ev-nachname'), validate: nameRule('Nachname') },
    'ev-email':    { el: () => byId('ev-email'),    err: () => byId('err-ev-email'),    validate: emailRule },
    'ev-marke':    { el: () => byId('ev-marke'),    err: () => byId('err-ev-marke'),    validate: requiredSelect('Bitte eine Marke wählen.') },
    'ev-modell':   { el: () => byId('ev-modell'),   err: () => byId('err-ev-modell'),   validate: (v) => {
        if (!v) return 'Modell ist erforderlich.';
        if (v.length < 2) return 'Mindestens 2 Zeichen.';
        if (v.length > 100) return 'Maximal 100 Zeichen.';
        return '';
      } },
    'ev-groesse':  { el: () => byId('ev-groesse'),  err: () => byId('err-ev-groesse'),  validate: requiredSelect('Bitte eine Schuhgrösse wählen.') },
    'ev-zustand':  { el: () => byId('ev-zustand'),  err: () => byId('err-ev-zustand'),  validate: requiredSelect('Bitte den Zustand wählen.') },
    'ev-preis':    { el: () => byId('ev-preis'),    err: () => byId('err-ev-preis'),    validate: (v) => {
        if (v === '') return 'Bitte einen Wunschpreis angeben.';
        const n = Number(v);
        if (isNaN(n) || n <= 0) return 'Bitte einen gültigen Preis (> 0) angeben.';
        if (n > 10000) return 'Maximal CHF 10000.';
        return '';
      } },
    'ev-agb':      { el: () => byId('ev-agb'),      err: () => byId('err-ev-agb'),      validate: () => byId('ev-agb').checked ? '' : 'Bitte AGB akzeptieren.' }
  }, 'verkauf-success');
}


/* ============================================================
   BAAM RUNNER – interaktives Spiel (Canvas)
   Steuerung: Leertaste / Pfeil ↑ / Klick / Touch
   ============================================================ */
(function initRunner() {
  const canvas = byId('runner-canvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  const W = canvas.width;
  const H = canvas.height;
  const groundY = H - 40;
  const INK = '#111111', RED = '#D42B2B', MID = '#999999';

  const player = { x: 64, y: groundY, w: 30, h: 38, vy: 0, jumping: false };
  const GRAVITY = 0.7, JUMP_V = -12.5;

  let obstacles = [], speed = 6, score = 0, highscore = 0, frame = 0;
  let state = 'idle';          // idle | running | over
  let raf = null;

  const scoreEl = byId('game-score');
  const highEl = byId('game-highscore');
  const startBtn = byId('game-start');
  const restartBtn = byId('game-restart');

  try { highscore = parseInt(localStorage.getItem('baam-highscore'), 10) || 0; } catch (e) {}
  highEl.textContent = pad(highscore);

  function pad(n) { return String(n).padStart(3, '0'); }

  function reset() {
    obstacles = []; speed = 6; score = 0; frame = 0;
    player.y = groundY; player.vy = 0; player.jumping = false;
    scoreEl.textContent = pad(0);
  }
  function jump() {
    if (state === 'running' && !player.jumping) {
      player.vy = JUMP_V;
      player.jumping = true;
    }
  }
  function start() {
    reset();
    state = 'running';
    canvas.focus();
    cancelAnimationFrame(raf);
    loop();
  }
  function gameOver() {
    state = 'over';
    cancelAnimationFrame(raf);
    if (score > highscore) {
      highscore = score;
      highEl.textContent = pad(highscore);
      try { localStorage.setItem('baam-highscore', String(highscore)); } catch (e) {}
    }
    draw();
  }
  function spawn() {
    const h = 24 + Math.random() * 28;
    obstacles.push({ x: W + 20, y: groundY - h, w: 14 + Math.random() * 12, h });
  }

  function update() {
    frame++;
    player.vy += GRAVITY;
    player.y += player.vy;
    if (player.y >= groundY) { player.y = groundY; player.vy = 0; player.jumping = false; }

    if (frame % Math.max(58, 110 - Math.floor(score / 40)) === 0) spawn();
    obstacles.forEach(o => { o.x -= speed; });
    obstacles = obstacles.filter(o => o.x + o.w > -10);

    if (frame % 6 === 0) { score++; scoreEl.textContent = pad(score); }
    if (frame % 600 === 0) speed += 0.5;

    const top = player.y - player.h;
    for (const o of obstacles) {
      if (player.x < o.x + o.w && player.x + player.w > o.x &&
          player.y > o.y && top < o.y + o.h) {
        gameOver();
        return;
      }
    }
  }

  function draw() {
    ctx.clearRect(0, 0, W, H);

    ctx.strokeStyle = INK;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(0, groundY + 1);
    ctx.lineTo(W, groundY + 1);
    ctx.stroke();

    ctx.fillStyle = INK;
    ctx.fillRect(player.x, player.y - player.h, player.w, player.h);
    ctx.fillStyle = RED;
    ctx.fillRect(player.x, player.y - 6, player.w, 6);

    ctx.fillStyle = RED;
    obstacles.forEach(o => ctx.fillRect(o.x, o.y, o.w, o.h));

    ctx.textAlign = 'center';
    if (state === 'idle') {
      ctx.fillStyle = INK;
      ctx.font = '700 20px Barlow, Arial, sans-serif';
      ctx.fillText('▶ Spielen oder Leertaste zum Starten', W / 2, H / 2 - 6);
      ctx.fillStyle = MID;
      ctx.font = '14px Barlow, Arial, sans-serif';
      ctx.fillText('Springe über die roten Hindernisse', W / 2, H / 2 + 18);
    } else if (state === 'over') {
      ctx.fillStyle = INK;
      ctx.font = '700 22px Barlow, Arial, sans-serif';
      ctx.fillText('GAME OVER · Score ' + pad(score), W / 2, H / 2 - 6);
      ctx.fillStyle = MID;
      ctx.font = '14px Barlow, Arial, sans-serif';
      ctx.fillText('Neustart oder Leertaste (Spielfeld) zum nochmal Spielen', W / 2, H / 2 + 18);
    }
    ctx.textAlign = 'start';
  }

  function loop() {
    if (state !== 'running') return;
    update();
    if (state !== 'running') return;
    draw();
    raf = requestAnimationFrame(loop);
  }

  draw();

  startBtn.addEventListener('click', start);
  restartBtn.addEventListener('click', start);
  canvas.addEventListener('click', () => { state === 'running' ? jump() : start(); });
  canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
    state === 'running' ? jump() : start();
  }, { passive: false });

  window.addEventListener('keydown', (e) => {
    if (e.code !== 'Space' && e.code !== 'ArrowUp') return;
    if (state === 'running') { e.preventDefault(); jump(); }
    else if (document.activeElement === canvas) { e.preventDefault(); start(); }
  });
})();
