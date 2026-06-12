<?php
/* ============================================================
   BAAM – Verkaufen (Einsende-Formular)
   ------------------------------------------------------------
   Speichert eine Einsendung über DREI Tabellen in EINER
   Transaktion:
     1) kunde      – per E-Mail wiederverwendet oder neu angelegt
     2) einsendung – Status 'eingegangen', Datum = heute
     3) produkt    – Modell, Marke, Grösse, Preis (grading folgt
                     später durch das BAAM-Team)
   Validierung im Browser per JavaScript (script.js); der Server
   prüft zusätzlich jedes Pflichtfeld erneut.
   ============================================================ */
require __DIR__ . '/db.php';

$NAME_RE    = '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\-]+$/u';
$EMAIL_RE   = '/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/';
$GROESSEN   = ['36','37','38','39','40','41','42','43','44','45','46','47'];
$MARKEN     = ['Nike','Adidas','Jordan','Yeezy','New Balance','Balenciaga','ASICS'];
$ZUSTAENDE  = ['neuwertig','sehr-gut','gut','gebraucht'];
$KATEGORIEN = ['Sneaker','Sport','Vintage','Designer','Alltag'];

$errors = [];
$old = [
  'vorname' => '', 'nachname' => '', 'email' => '', 'marke' => '', 'modell' => '',
  'groesse' => '', 'zustand' => '', 'preis' => '', 'kategorie' => '', 'agb' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach (['vorname','nachname','email','marke','modell','groesse','zustand','preis','kategorie'] as $k) {
    $old[$k] = trim($_POST[$k] ?? '');
  }
  $old['agb'] = isset($_POST['agb']);

  if ($old['vorname'] === '')                          $errors['vorname'] = 'Vorname ist erforderlich.';
  elseif (mb_strlen($old['vorname']) < 2)              $errors['vorname'] = 'Mindestens 2 Zeichen.';
  elseif (!preg_match($NAME_RE, $old['vorname']))     $errors['vorname'] = 'Nur Buchstaben und Bindestriche erlaubt.';

  if ($old['nachname'] === '')                         $errors['nachname'] = 'Nachname ist erforderlich.';
  elseif (mb_strlen($old['nachname']) < 2)             $errors['nachname'] = 'Mindestens 2 Zeichen.';
  elseif (!preg_match($NAME_RE, $old['nachname']))    $errors['nachname'] = 'Nur Buchstaben und Bindestriche erlaubt.';

  if ($old['email'] === '')                            $errors['email'] = 'E-Mail ist erforderlich.';
  elseif (mb_strlen($old['email']) > 100)              $errors['email'] = 'E-Mail darf maximal 100 Zeichen haben.';
  elseif (!preg_match($EMAIL_RE, $old['email']))      $errors['email'] = 'Bitte eine gültige E-Mail-Adresse eingeben.';

  if (!in_array($old['marke'], $MARKEN, true))         $errors['marke'] = 'Bitte eine Marke wählen.';

  if ($old['modell'] === '')                           $errors['modell'] = 'Modell ist erforderlich.';
  elseif (mb_strlen($old['modell']) < 2)               $errors['modell'] = 'Mindestens 2 Zeichen.';
  elseif (mb_strlen($old['modell']) > 100)             $errors['modell'] = 'Maximal 100 Zeichen.';

  if (!in_array($old['groesse'], $GROESSEN, true))     $errors['groesse'] = 'Bitte eine Schuhgrösse wählen.';

  if (!in_array($old['zustand'], $ZUSTAENDE, true))    $errors['zustand'] = 'Bitte den Zustand wählen.';

  if ($old['preis'] === '')                            $errors['preis'] = 'Bitte einen Wunschpreis angeben.';
  elseif (!is_numeric($old['preis']) || (float)$old['preis'] <= 0) $errors['preis'] = 'Bitte einen gültigen Preis (> 0) angeben.';
  elseif ((float)$old['preis'] > 10000)                $errors['preis'] = 'Maximal CHF 10000.';

  if ($old['kategorie'] !== '' && !in_array($old['kategorie'], $KATEGORIEN, true)) $errors['kategorie'] = 'Ungültige Kategorie.';

  if (!$old['agb'])                                    $errors['agb'] = 'Bitte AGB akzeptieren.';

  if (!$errors) {
    $pdo = db();
    if (!$pdo) {
      $errors['_global'] = 'Keine Datenbankverbindung: ' . ($GLOBALS['db_error'] ?? 'unbekannter Fehler');
    } else {
      try {
        $pdo->beginTransaction();

        /* 1) Kunde: bestehenden per E-Mail wiederverwenden, sonst neu anlegen */
        $sel = $pdo->prepare('SELECT kunde_id FROM kunde WHERE email = ?');
        $sel->execute([$old['email']]);
        $kunde_id = $sel->fetchColumn();
        if (!$kunde_id) {
          $ins = $pdo->prepare('INSERT INTO kunde (vorname, nachname, email) VALUES (?, ?, ?)');
          $ins->execute([$old['vorname'], $old['nachname'], $old['email']]);
          $kunde_id = (int) $pdo->lastInsertId();
        }

        /* 2) Einsendung anlegen (Status 'eingegangen', Datum heute) */
        $insE = $pdo->prepare("INSERT INTO einsendung (kunde_id, einsende_datum, einsende_status)
                               VALUES (?, CURDATE(), 'eingegangen')");
        $insE->execute([$kunde_id]);
        $einsendung_id = (int) $pdo->lastInsertId();

        /* 3) Marke nachschlagen oder neu anlegen (Jordan/Yeezy sind keine eigenen DB-Marken) */
        $selM = $pdo->prepare('SELECT marke_id FROM marke WHERE name = ?');
        $selM->execute([$old['marke']]);
        $marke_id = $selM->fetchColumn();
        if (!$marke_id) {
          $insM = $pdo->prepare('INSERT INTO marke (name) VALUES (?)');
          $insM->execute([$old['marke']]);
          $marke_id = (int) $pdo->lastInsertId();
        }

        /* 4) Produkt anlegen (grading bleibt NULL – vergibt das Team später) */
        $kategorie = $old['kategorie'] !== '' ? $old['kategorie'] : 'Sneaker';
        $insP = $pdo->prepare('INSERT INTO produkt (einsendung_id, name, kategorie, groesse_eu, preis, marke_id)
                               VALUES (?, ?, ?, ?, ?, ?)');
        $insP->execute([$einsendung_id, $old['modell'], $kategorie, $old['groesse'], $old['preis'], $marke_id]);

        $pdo->commit();
        header('Location: verkaufen.php?ok=1#einsenden');
        exit;
      } catch (PDOException $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors['_global'] = 'Fehler beim Speichern: ' . $ex->getMessage();
      }
    }
  }
}

$success = isset($_GET['ok']);

function fieldCls($errors, $key) { return isset($errors[$key]) ? ' error' : ''; }
function sel($a, $b) { return (string) $a === (string) $b ? ' selected' : ''; }

$pageTitle = 'BAAM – Verkaufen';
require __DIR__ . '/partials/header.php';
?>

  <!-- PAGE BANNER -->
  <section class="page-banner">
    <div class="container">
      <span class="eyebrow">Verkaufen</span>
      <h1>Verkaufe deine Sneaker</h1>
      <p>Schick deine Sneaker ein – unser Team prüft den Zustand, vergibt ein
         Grading (1–10) und stellt sie unter <strong>Community Shoes</strong> zum Verkauf.</p>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       EINSCHICKUNGSPROZESS
  ══════════════════════════════════════════════ -->
  <section class="section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">So funktioniert's</h2>
      </div>

      <div class="process-grid">
        <div class="process-step">
          <div class="process-num">1</div>
          <h3>Einsendung starten</h3>
          <p>Fülle das Formular unten aus. Du erhältst ein Versandlabel und
             eine Einsendungs-Nummer.</p>
        </div>
        <div class="process-step">
          <div class="process-num">2</div>
          <h3>Schuh einschicken</h3>
          <p>Verpacke deinen Sneaker sicher und sende ihn kostenlos an BAAM.</p>
        </div>
        <div class="process-step">
          <div class="process-num">3</div>
          <h3>Prüfung &amp; Grading</h3>
          <p>Unser Team prüft Zustand und Echtheit und vergibt ein
             transparentes Grading von 1 bis 10.</p>
        </div>
        <div class="process-step">
          <div class="process-num">4</div>
          <h3>Listung als Community Shoe</h3>
          <p>Nach erfolgreicher Prüfung wird dein Schuh im Sortiment unter
             „Community Shoes" gelistet.</p>
        </div>
        <div class="process-step">
          <div class="process-num">5</div>
          <h3>Verkauf &amp; Auszahlung</h3>
          <p>Sobald dein Sneaker verkauft ist, erhältst du deine Auszahlung –
             abzüglich der BAAM-Gebühr.</p>
        </div>
        <div class="process-step">
          <div class="process-num">★</div>
          <h3>Volle Transparenz</h3>
          <p>Den Status deiner Einsendung kannst du jederzeit nachverfolgen.</p>
        </div>
      </div>

      <h4 class="status-title">Status deiner Einsendung</h4>
      <div class="status-flow">
        <span class="status-chip ok">eingegangen</span>
        <span class="status-chip">in&nbsp;Prüfung</span>
        <span class="status-chip">gegradet</span>
        <span class="status-chip">verkauft</span>
        <span class="status-chip">abgelehnt</span>
      </div>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       EINSENDE-FORMULAR
  ══════════════════════════════════════════════ -->
  <section id="einsenden" class="sell-form-section">
    <div class="container">
      <div class="promo-layout">

        <!-- Links: Info -->
        <div class="promo-about">
          <h2>Sneaker einsenden</h2>
          <p>
            Gib hier die Daten zu deinem Sneaker an. Nach dem Absenden melden
            wir uns mit dem Versandlabel und allen weiteren Schritten.
          </p>
          <div class="promo-about-img">
            <img src="assets/images/hero-sneaker.svg" alt="Sneaker einsenden">
          </div>
          <ul class="promo-steps">
            <li>Kostenloser Versand zu BAAM</li>
            <li>Geprüftes, transparentes Grading</li>
            <li>Auszahlung nach erfolgreichem Verkauf</li>
          </ul>
        </div>

        <!-- Rechts: Formular -->
        <div class="form-card">
          <div class="form-card-head">
            <h2>Einsende-Formular</h2>
            <span class="form-badge">JS-Validierung + DB</span>
          </div>
          <p class="form-sub">Anbindung an die Datenbank (M290 – Tabellen „einsendung" &amp; „produkt").</p>

          <div id="verkauf-success" role="alert"<?= $success ? ' style="display:block;"' : '' ?>>
            ✓ Einsendung erfasst! Du erhältst gleich dein Versandlabel per E-Mail.
          </div>

          <?php if (isset($errors['_global'])): ?>
            <div class="form-error-box" role="alert"><?= e($errors['_global']) ?></div>
          <?php endif; ?>

          <!-- Validierung im Browser per JavaScript -> novalidate.
               Server-seitig wird zusätzlich geprüft und gespeichert. -->
          <form id="einsende-form" method="post" action="verkaufen.php#einsenden" novalidate>

            <div class="form-row">
              <div class="form-group">
                <label for="ev-vorname">Vorname<span class="req">*</span></label>
                <input type="text" id="ev-vorname" name="vorname" class="form-control<?= fieldCls($errors, 'vorname') ?>" placeholder="Max" value="<?= e($old['vorname']) ?>">
                <span class="field-error" id="err-ev-vorname"><?= e($errors['vorname'] ?? '') ?></span>
              </div>
              <div class="form-group">
                <label for="ev-nachname">Nachname<span class="req">*</span></label>
                <input type="text" id="ev-nachname" name="nachname" class="form-control<?= fieldCls($errors, 'nachname') ?>" placeholder="Mustermann" value="<?= e($old['nachname']) ?>">
                <span class="field-error" id="err-ev-nachname"><?= e($errors['nachname'] ?? '') ?></span>
              </div>
            </div>

            <div class="form-group">
              <label for="ev-email">E-Mail-Adresse<span class="req">*</span></label>
              <input type="text" id="ev-email" name="email" class="form-control<?= fieldCls($errors, 'email') ?>" placeholder="max@beispiel.ch" value="<?= e($old['email']) ?>">
              <span class="field-error" id="err-ev-email"><?= e($errors['email'] ?? '') ?></span>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="ev-marke">Marke<span class="req">*</span></label>
                <select id="ev-marke" name="marke" class="form-control<?= fieldCls($errors, 'marke') ?>">
                  <option value="">-- Marke wählen --</option>
                  <?php foreach (['Nike'=>'Nike','Adidas'=>'Adidas','Jordan'=>'Air Jordan','Yeezy'=>'Yeezy','New Balance'=>'New Balance','Balenciaga'=>'Balenciaga','ASICS'=>'ASICS'] as $val=>$lbl): ?>
                    <option value="<?= e($val) ?>"<?= sel($val, $old['marke']) ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="field-error" id="err-ev-marke"><?= e($errors['marke'] ?? '') ?></span>
              </div>
              <div class="form-group">
                <label for="ev-modell">Modell<span class="req">*</span></label>
                <input type="text" id="ev-modell" name="modell" class="form-control<?= fieldCls($errors, 'modell') ?>" placeholder="z.B. Dunk Low Black" value="<?= e($old['modell']) ?>">
                <span class="field-error" id="err-ev-modell"><?= e($errors['modell'] ?? '') ?></span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="ev-groesse">Schuhgrösse (EU)<span class="req">*</span></label>
                <select id="ev-groesse" name="groesse" class="form-control<?= fieldCls($errors, 'groesse') ?>">
                  <option value="">-- Grösse wählen --</option>
                  <?php foreach ($GROESSEN as $g): ?>
                    <option value="<?= $g ?>"<?= sel($g, $old['groesse']) ?>><?= $g ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="field-error" id="err-ev-groesse"><?= e($errors['groesse'] ?? '') ?></span>
              </div>
              <div class="form-group">
                <label for="ev-zustand">Zustand<span class="req">*</span></label>
                <select id="ev-zustand" name="zustand" class="form-control<?= fieldCls($errors, 'zustand') ?>">
                  <option value="">-- Zustand wählen --</option>
                  <?php foreach (['neuwertig'=>'Neuwertig (ungetragen)','sehr-gut'=>'Sehr gut','gut'=>'Gut','gebraucht'=>'Gebraucht'] as $val=>$lbl): ?>
                    <option value="<?= $val ?>"<?= sel($val, $old['zustand']) ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="field-error" id="err-ev-zustand"><?= e($errors['zustand'] ?? '') ?></span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="ev-preis">Wunschpreis (CHF)<span class="req">*</span></label>
                <input type="number" id="ev-preis" name="preis" class="form-control<?= fieldCls($errors, 'preis') ?>" placeholder="z.B. 180" min="0" value="<?= e($old['preis']) ?>">
                <span class="field-error" id="err-ev-preis"><?= e($errors['preis'] ?? '') ?></span>
              </div>
              <div class="form-group">
                <label for="ev-kategorie">Kategorie</label>
                <select id="ev-kategorie" name="kategorie" class="form-control">
                  <option value="">-- optional --</option>
                  <?php foreach ($KATEGORIEN as $kat): ?>
                    <option value="<?= $kat ?>"<?= sel($kat, $old['kategorie']) ?>><?= $kat ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <div class="form-checkbox">
                <input type="checkbox" id="ev-agb" name="agb"<?= $old['agb'] ? ' checked' : '' ?>>
                <label for="ev-agb">
                  Ich akzeptiere die <a href="#" style="text-decoration:underline;">AGB</a>
                  und bestätige, dass der Sneaker authentisch ist.<span class="req">*</span>
                </label>
              </div>
              <span class="field-error" id="err-ev-agb"><?= e($errors['agb'] ?? '') ?></span>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;text-align:center;">
              Einsendung starten →
            </button>

            <p class="form-db-note">
              DB-Anbindung: <code>INSERT INTO einsendung (kunde_id, einsende_datum, einsende_status)</code>
              und <code>INSERT INTO produkt (name, marke_id, groesse_eu, preis, grading)</code>.
            </p>
          </form>
        </div>

      </div>
    </div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>
