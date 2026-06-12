<?php
/* ============================================================
   BAAM – Datenbank-Verbindung (Plesk / MariaDB)
   ------------------------------------------------------------
   Verbindung via PDO – nach derselben Vorlage wie im Unterricht.
   Eingebunden von sortiment.php, promotionen.php und verkaufen.php
   mit:   require __DIR__ . '/db.php';
   ============================================================ */

/* ── Zugangsdaten der Plesk-Datenbank (vgl. DataGrip-Verbindung) ── */
$host = 'baam.christian-weibel1.bbzwinf.ch';
$port = 3306;
$db   = 'christian-weibel1_db';
$user = 'christian';
$pass = 'phpishass';   // ← Plesk-Passwort

/**
 * Baut die PDO-Verbindung einmalig auf und gibt sie zurück.
 * Scheitert die Verbindung, wird $GLOBALS['db_error'] gesetzt und
 * null zurückgegeben – die Seiten zeigen dann eine Hinweis-Meldung,
 * anstatt komplett abzubrechen.
 */
function db() {
  global $host, $port, $db, $user, $pass;
  static $pdo   = null;
  static $tried = false;

  if (!$tried) {
    $tried = true;
    try {
      $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
      );
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $pdo = null;
      $GLOBALS['db_error'] = $e->getMessage();
    }
  }
  return $pdo;
}

/** Sichere HTML-Ausgabe (Schutz vor XSS). */
function e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Wandelt einen Produktnamen in einen sauberen, eindeutigen Datei-Slug um.
 * Entfernt die "#N"-Endung (z.B. "Samba Panda #3" -> "samba-panda"),
 * ersetzt Umlaute/Akzente und reduziert auf [a-z0-9-].
 * Dieselbe Funktion nutzt der Bild-Importer (tools/import_images.php),
 * damit Dateiname und Suche garantiert übereinstimmen.
 */
function slugify($name) {
  $s = (string) $name;
  $s = preg_replace('/#\s*\d+\s*$/u', '', $s);   // "#3"-Endung entfernen
  $s = trim($s);
  $s = strtr($s, ['ä'=>'ae','ö'=>'oe','ü'=>'ue','Ä'=>'ae','Ö'=>'oe','Ü'=>'ue','ß'=>'ss']);
  $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
  if ($ascii !== false) $s = $ascii;
  $s = strtolower($s);
  $s = preg_replace('/[^a-z0-9]+/', '-', $s);
  $s = trim($s, '-');
  return $s !== '' ? $s : 'shoe';
}

/**
 * Ordnet einem Produktnamen das passende Bild zu – in dieser Reihenfolge:
 *   1) echtes, lokal gespeichertes Web-Foto  (assets/images/products/<slug>.{jpg,jpeg,png,webp,avif})
 *   2) SVG-Modell-Illustration als Fallback   (eine der 10 vorhandenen Modell-SVGs)
 *   3) allgemeiner Platzhalter
 * Die Web-Fotos werden von tools/import_images.php heruntergeladen.
 */
function produkt_bild($name) {
  $base = 'assets/images/products/';
  $slug = slugify($name);

  // 1) Echtes Web-Foto (über den Slug)
  foreach (['jpg', 'jpeg', 'png', 'webp', 'avif'] as $ext) {
    if (is_file(__DIR__ . '/' . $base . $slug . '.' . $ext)) {
      return $base . $slug . '.' . $ext;
    }
  }

  // 2) SVG-Modell-Illustration (Fallback nach Modell-Schlüsselwort)
  $map = [
    'air jordan 1'    => 'air-jordan-1',
    'air force 1'     => 'air-force-1',
    'dunk low'        => 'dunk-low',
    'yeezy 350'       => 'yeezy-350',
    'new balance 550' => 'new-balance-550',
    'gel-kayano 14'   => 'gel-kayano-14',
    'triple s'        => 'triple-s',
    'superstar'       => 'superstar',
    'gazelle'         => 'gazelle',
    'samba'           => 'samba',
  ];
  $low = mb_strtolower($name);
  foreach ($map as $needle => $s) {
    if (mb_strpos($low, $needle) !== false &&
        is_file(__DIR__ . '/' . $base . $s . '.svg')) {
      return $base . $s . '.svg';
    }
  }

  // 3) Allgemeiner Platzhalter
  return 'assets/images/product-placeholder.svg';
}
