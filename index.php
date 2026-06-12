<?php
/* ============================================================
   BAAM – Startseite (Home)
   ============================================================ */
require __DIR__ . '/db.php';
$pageTitle = 'BAAM – Sneaker Marketplace | Home';
require __DIR__ . '/partials/header.php';
?>

  <!-- ══════════════════════════════════════════════
       HERO
  ══════════════════════════════════════════════ -->
  <section id="hero" aria-label="Hero">
    <div class="hero-image">
      <?php
        /* Echtes Hero-Foto (von tools/import_hero.php geladen) bevorzugen,
           sonst auf die SVG-Illustration zurückfallen. */
        $heroImg = 'assets/images/hero-sneaker.svg';
        foreach (['webp', 'jpg', 'jpeg', 'png', 'avif'] as $ext) {
          if (is_file(__DIR__ . '/assets/images/hero.' . $ext)) { $heroImg = 'assets/images/hero.' . $ext; break; }
        }
      ?>
      <img src="<?= e($heroImg) ?>" alt="Zwei Sneaker vor kräftigem rotem Hintergrund – BAAM Marketplace">
    </div>
    <div class="hero-content">
      <span class="hero-eyebrow">BAAM Sneaker Marketplace</span>
      <h1 class="hero-title">
        BAAM – Dein<br><em>Sneaker</em><br>Marktplatz
      </h1>
      <p class="hero-text">
        Transparenter Handel für limitierte Sneaker und Streetwear.
        Jede Aktion ist nachvollziehbar – mit integriertem Rating-System
        für Zustand, Qualität und Authentizität.
      </p>
      <div class="hero-actions">
        <a href="sortiment.php" class="btn-primary">Jetzt entdecken →</a>
        <a href="verkaufen.php" class="btn-outline">Verkaufen</a>
      </div>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       SORTIMENT-VORSCHAU
  ══════════════════════════════════════════════ -->
  <section class="section section-products">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Neu von BAAM</h2>
        <a href="sortiment.php" class="section-link">Alle Produkte →</a>
      </div>

      <!-- Brand-Chips (auf der Startseite rein visuell – Filter siehe Sortiment) -->
      <div class="brand-chips home-chips">
        <button class="brand-chip active">Alle</button>
        <button class="brand-chip">Nike</button>
        <button class="brand-chip">Adidas</button>
        <button class="brand-chip">Yeezy</button>
        <button class="brand-chip">Jordan</button>
        <button class="brand-chip">New Balance</button>
      </div>

      <div class="product-grid">

        <article class="product-card" data-brand="Nike">
          <div class="product-img"><img src="<?= e(produkt_bild('Nike Dunk Low Black')) ?>" alt="Nike Dunk Low Black"></div>
          <div class="product-info">
            <p class="product-brand">Nike</p>
            <h3 class="product-name">Dunk Low Black</h3>
            <p class="product-colorway">Black / White</p>
            <div class="product-footer">
              <span class="product-price">CHF 149</span>
              <div><div class="rating-badge high">8</div><div class="rating-label">Rating</div></div>
            </div>
          </div>
          <a href="sortiment.php" class="product-buy">Kaufen</a>
        </article>

        <article class="product-card" data-brand="Adidas">
          <div class="product-img"><img src="<?= e(produkt_bild('Adidas Samba Panda')) ?>" alt="Adidas Samba Panda"></div>
          <div class="product-info">
            <p class="product-brand">Adidas</p>
            <h3 class="product-name">Samba Panda</h3>
            <p class="product-colorway">Core Black / Cloud White</p>
            <div class="product-footer">
              <span class="product-price">CHF 120</span>
              <div><div class="rating-badge high">9</div><div class="rating-label">Rating</div></div>
            </div>
          </div>
          <a href="sortiment.php" class="product-buy">Kaufen</a>
        </article>

        <article class="product-card" data-brand="Jordan">
          <div class="product-img"><img src="<?= e(produkt_bild('Air Jordan 1 Red')) ?>" alt="Air Jordan 1 Red"></div>
          <div class="product-info">
            <p class="product-brand">Nike / Jordan</p>
            <h3 class="product-name">Air Jordan 1 Red</h3>
            <p class="product-colorway">Varsity Red / Black</p>
            <div class="product-footer">
              <span class="product-price">CHF 220</span>
              <div><div class="rating-badge high">7</div><div class="rating-label">Rating</div></div>
            </div>
          </div>
          <a href="sortiment.php" class="product-buy">Kaufen</a>
        </article>

        <article class="product-card" data-brand="Yeezy">
          <div class="product-img"><img src="<?= e(produkt_bild('Yeezy 350 Granite')) ?>" alt="Yeezy 350 Granite"></div>
          <div class="product-info">
            <p class="product-brand">Adidas / Yeezy</p>
            <h3 class="product-name">Yeezy 350 Granite</h3>
            <p class="product-colorway">Granite / Beige</p>
            <div class="product-footer">
              <span class="product-price">CHF 350</span>
              <div><div class="rating-badge high">9</div><div class="rating-label">Rating</div></div>
            </div>
          </div>
          <a href="sortiment.php" class="product-buy">Kaufen</a>
        </article>

      </div>
      <p class="rating-hint">Das Grading-Badge (1–10) ist das Vertrauens-Merkmal von BAAM – es bewertet Zustand, Qualität und Authentizität.</p>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       KAUFEN & VERKAUFEN – die zwei Kategorien
  ══════════════════════════════════════════════ -->
  <section class="about-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Kaufen &amp; Verkaufen</h2>
      </div>
      <div class="about-grid">
        <div class="about-card">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 12.5l8-8a2 2 0 0 1 1.4-.6H19a1.5 1.5 0 0 1 1.5 1.5v5.6a2 2 0 0 1-.6 1.4l-8 8a1.5 1.5 0 0 1-2.1 0l-6.3-6.3a1.5 1.5 0 0 1 0-2.1z"/><circle cx="16" cy="8" r="1.3"/></svg>
          </span>
          <h3>Neu von BAAM</h3>
          <p>Brandneue, originalverpackte Sneaker direkt von uns – authentisch
             und sofort lieferbar.</p>
          <a href="sortiment.php" class="section-link" style="border:none;">Zum Sortiment →</a>
        </div>
        <div class="about-card">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3.2 3.2 0 0 1 0 5.6"/><path d="M17.5 13.5a5.5 5.5 0 0 1 3 5.5"/></svg>
          </span>
          <h3>Community Shoes</h3>
          <p>Von Mitgliedern eingeschickt, von unserem Team geprüft und mit
             einem Grading (1–10) bewertet.</p>
          <a href="sortiment.php#community" class="section-link" style="border:none;">Community Shoes →</a>
        </div>
        <div class="about-card">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14v4.5A1.5 1.5 0 0 0 5.5 20h13a1.5 1.5 0 0 0 1.5-1.5V14"/><path d="M12 15V4"/><path d="M8 8l4-4 4 4"/></svg>
          </span>
          <h3>Selbst verkaufen</h3>
          <p>Schick deine Sneaker ein – wir prüfen sie und stellen sie unter
             Community Shoes zum Verkauf.</p>
          <a href="verkaufen.php" class="section-link" style="border:none;">So funktioniert's →</a>
        </div>
      </div>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       AKTUELLE PROMOTIONEN
  ══════════════════════════════════════════════ -->
  <section class="section section-promo">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Aktuelle Promotionen</h2>
        <a href="promotionen.php" class="section-link" style="color:var(--clr-red);">Alle Aktionen →</a>
      </div>
      <div class="promo-banner">
        <span class="promo-tag">Aktionswoche</span>
        <h3 class="promo-title">Jetzt limitierte Sneaker sichern –<br>nur für kurze Zeit!</h3>
        <p class="promo-text">
          Exklusive Zugänge zu limitierten Sneaker-Drops und das interaktive
          BAAM Runner-Game. Melde dich an und sei als Erster dabei.
        </p>
        <a href="promotionen.php" class="btn-primary">Zur Promotion →</a>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>
