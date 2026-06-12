<?php
/* ============================================================
   BAAM – Sortiment
   ------------------------------------------------------------
   "Neu von BAAM"  : eigenes Neuware-Sortiment (statisch).
   "Community Shoes": aus der Datenbank (Tabelle produkt) gerendert –
                      eingeschickt von Mitgliedern, vom Team gegradet.
   ============================================================ */
require __DIR__ . '/db.php';

/* Leitet aus dem Freitext-Farbnamen einen Filter-Slug ab,
   damit der bestehende Colorway-Filter (script.js) greift. */
function colorway_slug($farbe) {
  $f = mb_strtolower((string) $farbe);
  if (strpos($f, 'black') !== false && strpos($f, 'white') !== false) return 'black-white';
  if (strpos($f, 'white') !== false || strpos($f, 'weiss') !== false) return 'triple-white';
  if (strpos($f, 'beige') !== false || strpos($f, 'granite') !== false || strpos($f, 'sand') !== false) return 'beige';
  if (strpos($f, 'blue') !== false || strpos($f, 'blau') !== false) return 'blue';
  if (strpos($f, 'red') !== false || strpos($f, 'rot') !== false) return 'red';
  return '';
}

/* CHF-Preis hübsch: ganze Zahl ohne Dezimalstellen, sonst mit zweien. */
function chf($preis) {
  $p = (float) $preis;
  return ($p == floor($p)) ? number_format($p, 0, '.', "'") : number_format($p, 2, '.', "'");
}

/* ── Community Shoes aus der DB laden ── */
$community = [];
$db_problem = null;
$pdo = db();
if (!$pdo) {
  $db_problem = $GLOBALS['db_error'] ?? 'Keine Datenbankverbindung.';
} else {
  try {
    $sql = "SELECT p.produkt_id, p.name, p.kategorie, p.groesse_eu, p.farbe,
                   p.preis, p.grading, m.name AS marke
            FROM produkt p
            LEFT JOIN marke m       ON m.marke_id = p.marke_id
            JOIN einsendung e       ON e.einsendung_id = p.einsendung_id
            WHERE p.grading IS NOT NULL
            ORDER BY p.grading DESC, p.produkt_id DESC";
    $community = $pdo->query($sql)->fetchAll();
  } catch (PDOException $ex) {
    $db_problem = $ex->getMessage();
  }
}

$pageTitle = 'BAAM – Sortiment';
require __DIR__ . '/partials/header.php';
?>

  <!-- ══════════════════════════════════════════════
       IMAGE-SLIDER (Featured Sneaker)
  ══════════════════════════════════════════════ -->
  <section class="slider-section">
    <div class="slider" id="hero-slider" aria-roledescription="Karussell" aria-label="Featured Sneaker">
      <div class="slides">
        <div class="slide slide-1">
          <div class="slide-inner">
            <span class="slide-label">Featured Sneaker · 1 / 3</span>
            <h2>Air Jordan 1 Red</h2>
            <p>Community Shoe – Grading 7 / 10, geprüft von BAAM</p>
          </div>
        </div>
        <div class="slide slide-2">
          <div class="slide-inner">
            <span class="slide-label">Featured Sneaker · 2 / 3</span>
            <h2>Nike Dunk Low Black</h2>
            <p>Neu von BAAM – originalverpackt &amp; sofort lieferbar</p>
          </div>
        </div>
        <div class="slide slide-3">
          <div class="slide-inner">
            <span class="slide-label">Featured Sneaker · 3 / 3</span>
            <h2>Yeezy 350 Granite</h2>
            <p>Community Shoe – limitiert, Grading 9 / 10</p>
          </div>
        </div>
      </div>
      <button class="slider-btn prev" id="slider-prev" aria-label="Vorheriger Slide">‹</button>
      <button class="slider-btn next" id="slider-next" aria-label="Nächster Slide">›</button>
      <div class="slider-dots" id="slider-dots" aria-label="Slide-Auswahl"></div>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       SORTIMENT: FILTER + ZWEI ABSCHNITTE
  ══════════════════════════════════════════════ -->
  <section class="section">
    <div class="container">
      <div class="sortiment-layout">

        <!-- FILTER-SIDEBAR (wirkt über beide Abschnitte) -->
        <aside class="filter-sidebar" aria-label="Produktfilter">
          <h3 class="filter-title">Filter</h3>

          <div class="filter-group">
            <h4>Marke</h4>
            <label class="check"><input type="checkbox" class="f-brand" value="Nike"> Nike</label>
            <label class="check"><input type="checkbox" class="f-brand" value="Adidas"> Adidas</label>
            <label class="check"><input type="checkbox" class="f-brand" value="Yeezy"> Yeezy</label>
            <label class="check"><input type="checkbox" class="f-brand" value="Jordan"> Jordan</label>
            <label class="check"><input type="checkbox" class="f-brand" value="New Balance"> New Balance</label>
            <label class="check"><input type="checkbox" class="f-brand" value="Balenciaga"> Balenciaga</label>
            <label class="check"><input type="checkbox" class="f-brand" value="ASICS"> ASICS</label>
          </div>

          <div class="filter-group">
            <h4>Preis (CHF)</h4>
            <div class="price-row">
              <input type="number" id="f-price-min" class="price-input" placeholder="0" min="0">
              <span>–</span>
              <input type="number" id="f-price-max" class="price-input" placeholder="500" min="0">
            </div>
          </div>

          <div class="filter-group">
            <h4>Grading (1–10)</h4>
            <div class="rating-btns">
              <button type="button" class="f-rating" data-min="1" data-max="3">1–3</button>
              <button type="button" class="f-rating" data-min="4" data-max="6">4–6</button>
              <button type="button" class="f-rating" data-min="7" data-max="8">7–8</button>
              <button type="button" class="f-rating" data-min="9" data-max="10">9–10</button>
            </div>
          </div>

          <div class="filter-group">
            <h4>Colorway</h4>
            <label class="check"><input type="checkbox" class="f-colorway" value="black-white"> Black / White</label>
            <label class="check"><input type="checkbox" class="f-colorway" value="triple-white"> Triple White</label>
            <label class="check"><input type="checkbox" class="f-colorway" value="beige"> Beige</label>
            <label class="check"><input type="checkbox" class="f-colorway" value="blue"> Blue</label>
            <label class="check"><input type="checkbox" class="f-colorway" value="red"> Red</label>
          </div>

          <button type="button" id="f-reset" class="filter-reset">Filter zurücksetzen</button>
        </aside>


        <!-- HAUPTBEREICH -->
        <div class="sortiment-main">

          <div class="toolbar">
            <div class="search-box">
              <input type="text" id="product-search" placeholder="Sneaker suchen…" aria-label="Sneaker suchen">
              <button type="button" id="search-btn" aria-label="Suchen">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
              </button>
            </div>
            <select id="product-sort" class="form-control" aria-label="Sortierung">
              <option value="price-asc">Preis aufsteigend</option>
              <option value="price-desc">Preis absteigend</option>
              <option value="rating-desc">Grading: hoch → tief</option>
              <option value="name-asc">Name: A → Z</option>
            </select>
          </div>

          <!-- ABSCHNITT 1: NEU VON BAAM (statisches Neuware-Sortiment) -->
          <div class="shop-section">
            <div class="sortiment-head">
              <h2 class="section-title">Neu von BAAM</h2>
              <span class="result-count"><span class="sec-count">6</span> Modelle</span>
            </div>
            <p class="section-desc">
              Brandneue Sneaker direkt von BAAM – originalverpackt, authentisch und sofort lieferbar.
            </p>

            <div class="product-grid sortiment-grid" data-section="neu">

              <article class="product-card" data-brand="Nike" data-price="149" data-rating="10" data-colorway="black-white" data-name="Nike Dunk Low Black">
                <div class="product-img">
                  <span class="card-tag tag-new">Neu</span>
                  <img src="<?= e(produkt_bild('Nike Dunk Low Black')) ?>" alt="Nike Dunk Low Black">
                </div>
                <div class="product-info">
                  <p class="product-brand">Nike</p>
                  <h3 class="product-name">Dunk Low Black</h3>
                  <p class="product-colorway">Black / White</p>
                  <div class="product-footer">
                    <span class="product-price">CHF 149</span>
                    <div><div class="rating-badge high">10</div><div class="rating-label">Neu</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>

              <article class="product-card" data-brand="Adidas" data-price="120" data-rating="10" data-colorway="black-white" data-name="Adidas Samba Panda">
                <div class="product-img">
                  <span class="card-tag tag-new">Neu</span>
                  <img src="<?= e(produkt_bild('Adidas Samba Panda')) ?>" alt="Adidas Samba Panda">
                </div>
                <div class="product-info">
                  <p class="product-brand">Adidas</p>
                  <h3 class="product-name">Samba Panda</h3>
                  <p class="product-colorway">Core Black / Cloud White</p>
                  <div class="product-footer">
                    <span class="product-price">CHF 120</span>
                    <div><div class="rating-badge high">10</div><div class="rating-label">Neu</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>

              <article class="product-card" data-brand="New Balance" data-price="180" data-rating="10" data-colorway="triple-white" data-name="New Balance 550 Triple White">
                <div class="product-img">
                  <span class="card-tag tag-new">Neu</span>
                  <img src="<?= e(produkt_bild('New Balance 550 Triple White')) ?>" alt="New Balance 550 Triple White">
                </div>
                <div class="product-info">
                  <p class="product-brand">New Balance</p>
                  <h3 class="product-name">550 Triple White</h3>
                  <p class="product-colorway">White / White</p>
                  <div class="product-footer">
                    <span class="product-price">CHF 180</span>
                    <div><div class="rating-badge high">10</div><div class="rating-label">Neu</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>

              <article class="product-card" data-brand="Nike" data-price="130" data-rating="10" data-colorway="black-white" data-name="Nike Air Force 1 Panda">
                <div class="product-img">
                  <span class="card-tag tag-new">Neu</span>
                  <img src="<?= e(produkt_bild('Nike Air Force 1 Panda')) ?>" alt="Nike Air Force 1 Panda">
                </div>
                <div class="product-info">
                  <p class="product-brand">Nike</p>
                  <h3 class="product-name">Air Force 1 Panda</h3>
                  <p class="product-colorway">White / Black</p>
                  <div class="product-footer">
                    <span class="product-price">CHF 130</span>
                    <div><div class="rating-badge high">10</div><div class="rating-label">Neu</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>

              <article class="product-card" data-brand="Nike" data-price="159" data-rating="10" data-colorway="blue" data-name="Nike Dunk Low Blue">
                <div class="product-img">
                  <span class="card-tag tag-new">Neu</span>
                  <img src="<?= e(produkt_bild('Nike Dunk Low Blue')) ?>" alt="Nike Dunk Low Blue">
                </div>
                <div class="product-info">
                  <p class="product-brand">Nike</p>
                  <h3 class="product-name">Dunk Low Blue</h3>
                  <p class="product-colorway">University Blue / White</p>
                  <div class="product-footer">
                    <span class="product-price">CHF 159</span>
                    <div><div class="rating-badge high">10</div><div class="rating-label">Neu</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>

              <article class="product-card" data-brand="ASICS" data-price="195" data-rating="10" data-colorway="red" data-name="ASICS Gel-Kayano 14 Red">
                <div class="product-img">
                  <span class="card-tag tag-new">Neu</span>
                  <img src="<?= e(produkt_bild('ASICS Gel-Kayano 14 Red')) ?>" alt="ASICS Gel-Kayano 14 Red">
                </div>
                <div class="product-info">
                  <p class="product-brand">ASICS</p>
                  <h3 class="product-name">Gel-Kayano 14 Red</h3>
                  <p class="product-colorway">Classic Red / Silver</p>
                  <div class="product-footer">
                    <span class="product-price">CHF 195</span>
                    <div><div class="rating-badge high">10</div><div class="rating-label">Neu</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>

            </div>
            <p class="no-results sec-empty" hidden>Keine Treffer in „Neu von BAAM" – Filter anpassen.</p>
          </div>


          <!-- ABSCHNITT 2: COMMUNITY SHOES (aus der Datenbank) -->
          <div class="shop-section" id="community">
            <div class="sortiment-head">
              <h2 class="section-title">Community Shoes</h2>
              <span class="result-count"><span class="sec-count"><?= count($community) ?></span> Modelle</span>
            </div>
            <p class="section-desc">
              Von Mitgliedern eingeschickt, von unserem Team auf Zustand und Echtheit geprüft
              und mit einem Grading (1–10) bewertet.
              <a href="verkaufen.php" class="section-link" style="border:none;">Selbst verkaufen →</a>
            </p>

            <?php if ($db_problem): ?>
              <p class="no-results" style="display:block;">
                Community Shoes konnten nicht aus der Datenbank geladen werden.
                <br><small><?= e($db_problem) ?></small>
              </p>
            <?php endif; ?>

            <div class="product-grid sortiment-grid" data-section="community">
              <?php foreach ($community as $p):
                $marke   = $p['marke'] ?: 'BAAM';
                $rating  = (int) $p['grading'];
                $slug    = colorway_slug($p['farbe']);
                $badgeCl = $rating >= 7 ? 'rating-badge high' : 'rating-badge';
              ?>
              <article class="product-card"
                       data-brand="<?= e($marke) ?>"
                       data-price="<?= e($p['preis']) ?>"
                       data-rating="<?= $rating ?>"
                       data-colorway="<?= e($slug) ?>"
                       data-name="<?= e($marke . ' ' . $p['name']) ?>">
                <div class="product-img">
                  <span class="card-tag tag-community">✓ Geprüft</span>
                  <img src="<?= e(produkt_bild($marke . ' ' . $p['name'])) ?>" alt="<?= e($marke . ' ' . $p['name']) ?>">
                </div>
                <div class="product-info">
                  <p class="product-brand"><?= e($marke) ?> · Community</p>
                  <h3 class="product-name"><?= e($p['name']) ?></h3>
                  <p class="product-colorway"><?= e($p['farbe'] ?: $p['kategorie']) ?></p>
                  <div class="product-footer">
                    <span class="product-price">CHF <?= e(chf($p['preis'])) ?></span>
                    <div><div class="<?= $badgeCl ?>"><?= $rating ?></div><div class="rating-label">Grading</div></div>
                  </div>
                </div>
                <a href="#" class="product-buy">Kaufen</a>
              </article>
              <?php endforeach; ?>
            </div>
            <p class="no-results sec-empty"<?= count($community) ? ' hidden' : '' ?>>
              <?= $db_problem ? 'Keine Daten verfügbar.' : 'Keine Treffer in „Community Shoes" – Filter anpassen.' ?>
            </p>
          </div>

        </div><!-- .sortiment-main -->
      </div><!-- .sortiment-layout -->
    </div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>
