<?php
/* ============================================================
   BAAM – Promotionen (Anmeldeformular -> Tabelle "kunde")
   ------------------------------------------------------------
   Validierung im Browser per JavaScript (script.js). Zusätzlich
   prüft der Server hier jedes Pflichtfeld erneut (man darf den
   Eingaben aus dem Browser nie blind vertrauen) und speichert
   anschliessend mit einem vorbereiteten PDO-Statement.
   PRG-Muster: Bei Erfolg Redirect auf ?ok=1 (kein Doppel-Insert).
   ============================================================ */
require __DIR__ . '/db.php';

$NAME_RE  = '/^[A-Za-zÀ-ÖØ-öø-ÿ\s\-]+$/u';
$EMAIL_RE = '/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/';
$GROESSEN = ['36','37','38','39','40','41','42','43','44','45','46','47'];

$errors = [];
$old = ['vorname' => '', 'nachname' => '', 'email' => '', 'schuhgroesse' => '', 'marke' => '', 'agb' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old['vorname']      = trim($_POST['vorname'] ?? '');
  $old['nachname']     = trim($_POST['nachname'] ?? '');
  $old['email']        = trim($_POST['email'] ?? '');
  $old['schuhgroesse'] = trim($_POST['schuhgroesse'] ?? '');
  $old['marke']        = trim($_POST['marke'] ?? '');
  $old['agb']          = isset($_POST['agb']);

  if ($old['vorname'] === '')                                   $errors['vorname'] = 'Vorname ist erforderlich.';
  elseif (mb_strlen($old['vorname']) < 2)                       $errors['vorname'] = 'Mindestens 2 Zeichen.';
  elseif (!preg_match($NAME_RE, $old['vorname']))              $errors['vorname'] = 'Nur Buchstaben und Bindestriche erlaubt.';

  if ($old['nachname'] === '')                                  $errors['nachname'] = 'Nachname ist erforderlich.';
  elseif (mb_strlen($old['nachname']) < 2)                      $errors['nachname'] = 'Mindestens 2 Zeichen.';
  elseif (!preg_match($NAME_RE, $old['nachname']))             $errors['nachname'] = 'Nur Buchstaben und Bindestriche erlaubt.';

  if ($old['email'] === '')                                     $errors['email'] = 'E-Mail ist erforderlich.';
  elseif (mb_strlen($old['email']) > 100)                       $errors['email'] = 'E-Mail darf maximal 100 Zeichen haben.';
  elseif (!preg_match($EMAIL_RE, $old['email']))               $errors['email'] = 'Bitte eine gültige E-Mail-Adresse eingeben.';

  if (!in_array($old['schuhgroesse'], $GROESSEN, true))         $errors['schuhgroesse'] = 'Bitte eine Schuhgrösse auswählen.';

  if (!$old['agb'])                                             $errors['agb'] = 'Bitte akzeptiere die AGB.';

  if (!$errors) {
    $pdo = db();
    if (!$pdo) {
      $errors['_global'] = 'Keine Datenbankverbindung: ' . ($GLOBALS['db_error'] ?? 'unbekannter Fehler');
    } else {
      try {
        $stmt = $pdo->prepare('INSERT INTO kunde (vorname, nachname, email) VALUES (?, ?, ?)');
        $stmt->execute([$old['vorname'], $old['nachname'], $old['email']]);
        header('Location: promotionen.php?ok=1#formular');
        exit;
      } catch (PDOException $ex) {
        if ($ex->getCode() === '23000') {
          $errors['email'] = 'Diese E-Mail ist bereits registriert (UNIQUE Constraint).';
        } else {
          $errors['_global'] = 'Fehler beim Speichern: ' . $ex->getMessage();
        }
      }
    }
  }
}

$success = isset($_GET['ok']);

/* kleine Helfer für die Wiederbefüllung */
function fieldCls($errors, $key) { return isset($errors[$key]) ? ' error' : ''; }
function sel($a, $b) { return (string) $a === (string) $b ? ' selected' : ''; }

$pageTitle = 'BAAM – Promotionen';
require __DIR__ . '/partials/header.php';
?>

  <!-- ══════════════════════════════════════════════
       PROMO HERO – AKTIONSWOCHE
  ══════════════════════════════════════════════ -->
  <section class="promo-hero">
    <div class="container">
      <h1>BAAM Aktionswoche</h1>
      <p class="promo-hero-sub">[ Promotion Hero Banner ] · Limitierte Sneaker – jetzt mitmachen!</p>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       ÜBER DIE PROMOTION  +  ANMELDEFORMULAR
  ══════════════════════════════════════════════ -->
  <section id="formular" class="section">
    <div class="container">
      <div class="promo-layout">

        <!-- Links: Über die Promotion -->
        <div class="promo-about">
          <h2>Über die Promotion</h2>
          <div class="promo-about-img">
            <img src="assets/images/promo.jpg" alt="Nike Sneaker – limitierte Aktion bei BAAM">
          </div>

          <h3>Was gibt es zu gewinnen?</h3>
          <p>
            In der Aktionswoche verlosen wir limitierte Sneaker-Drops unter
            allen angemeldeten Mitgliedern. Zusätzlich erhältst du als
            Neumitglied frühzeitigen Zugang zu kommenden Releases.
          </p>

          <h3>Wie mitmachen?</h3>
          <ul class="promo-steps">
            <li>Melde dich rechts mit dem Anmeldeformular an.</li>
            <li>Bestätige die AGB und sende das Formular ab.</li>
            <li>Erhalte deine Bestätigung und sei beim nächsten Drop dabei.</li>
          </ul>
        </div>

        <!-- Rechts: Anmeldeformular -->
        <div class="form-card">
          <div class="form-card-head">
            <h2>Anmeldeformular</h2>
          </div>

          <div id="form-success" role="alert"<?= $success ? ' style="display:block;"' : '' ?>>
            ✓ Anmeldung erfolgreich! Wir melden uns bald bei dir.
          </div>

          <?php if (isset($errors['_global'])): ?>
            <div class="form-error-box" role="alert"><?= e($errors['_global']) ?></div>
          <?php endif; ?>

          <!-- Validierung im Browser per JavaScript -> novalidate.
               Server-seitig wird zusätzlich geprüft und gespeichert. -->
          <form id="anmelde-form" method="post" action="promotionen.php#formular" novalidate>

            <div class="form-row">
              <div class="form-group">
                <label for="vorname">Vorname<span class="req">*</span></label>
                <input type="text" id="vorname" name="vorname" class="form-control<?= fieldCls($errors, 'vorname') ?>"
                       placeholder="Max" autocomplete="given-name" value="<?= e($old['vorname']) ?>">
                <span class="field-error" id="err-vorname"><?= e($errors['vorname'] ?? '') ?></span>
              </div>
              <div class="form-group">
                <label for="nachname">Nachname<span class="req">*</span></label>
                <input type="text" id="nachname" name="nachname" class="form-control<?= fieldCls($errors, 'nachname') ?>"
                       placeholder="Mustermann" autocomplete="family-name" value="<?= e($old['nachname']) ?>">
                <span class="field-error" id="err-nachname"><?= e($errors['nachname'] ?? '') ?></span>
              </div>
            </div>

            <div class="form-group">
              <label for="email">E-Mail-Adresse<span class="req">*</span></label>
              <input type="text" id="email" name="email" class="form-control<?= fieldCls($errors, 'email') ?>"
                     placeholder="max@beispiel.ch" autocomplete="email" value="<?= e($old['email']) ?>">
              <span class="field-error" id="err-email"><?= e($errors['email'] ?? '') ?></span>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="schuhgroesse">Schuhgrösse<span class="req">*</span></label>
                <select id="schuhgroesse" name="schuhgroesse" class="form-control<?= fieldCls($errors, 'schuhgroesse') ?>">
                  <option value="">-- Grösse wählen --</option>
                  <?php foreach ($GROESSEN as $g): ?>
                    <option value="<?= $g ?>"<?= sel($g, $old['schuhgroesse']) ?>><?= $g ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="field-error" id="err-schuhgroesse"><?= e($errors['schuhgroesse'] ?? '') ?></span>
              </div>
              <div class="form-group">
                <label for="marke">Lieblingsmarke</label>
                <select id="marke" name="marke" class="form-control">
                  <option value="">-- optional --</option>
                  <?php foreach (['nike'=>'Nike','adidas'=>'Adidas','jordan'=>'Air Jordan','yeezy'=>'Yeezy','new-balance'=>'New Balance','balenciaga'=>'Balenciaga','asics'=>'ASICS'] as $val=>$lbl): ?>
                    <option value="<?= $val ?>"<?= sel($val, $old['marke']) ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <div class="form-checkbox">
                <input type="checkbox" id="agb" name="agb"<?= $old['agb'] ? ' checked' : '' ?>>
                <label for="agb">
                  Ich akzeptiere die <a href="#" style="text-decoration:underline;">AGB</a>
                  und Datenschutzbestimmungen von BAAM.<span class="req">*</span>
                </label>
              </div>
              <span class="field-error" id="err-agb"><?= e($errors['agb'] ?? '') ?></span>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;text-align:center;">
              Jetzt anmelden →
            </button>

          </form>
        </div>

      </div>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════
       INTERAKTIVES SPIEL – BAAM RUNNER
  ══════════════════════════════════════════════ -->
  <section class="game-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Interaktives Spiel</h2>
        <span class="game-badge">BAAM Runner</span>
      </div>
      <p class="game-sub">
        Runner-Game – reagiert auf Tastendruck (Leertaste / Pfeil ↑) und Klick / Touch.
      </p>

      <div class="game-wrap">
        <div class="game-hud">
          <span>SCORE: <strong id="game-score">000</strong></span>
          <span>HIGHSCORE: <strong id="game-highscore">000</strong></span>
        </div>
        <canvas id="runner-canvas" width="900" height="240" tabindex="0"
                aria-label="BAAM Runner Game"></canvas>
        <div class="game-controls">
          <button type="button" id="game-start" class="btn-primary btn-icon">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true"><path d="M7 5l12 7-12 7z"/></svg>Spielen</button>
          <button type="button" id="game-restart" class="btn-outline-dark btn-icon">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 4v5h-5"/></svg>Neustart</button>
          <span class="game-hint">Leertaste / Pfeil ↑ / Klick zum Springen</span>
        </div>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>
