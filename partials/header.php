<?php
/* ============================================================
   BAAM – gemeinsamer Seitenkopf (Header + Navigation)
   ------------------------------------------------------------
   Eingebunden mit:
     require __DIR__ . '/db.php';
     $pageTitle = 'BAAM – …';
     require __DIR__ . '/partials/header.php';
   Erwartet, dass db.php (für e()) bereits geladen ist.
   ============================================================ */
if (!function_exists('e')) { require __DIR__ . '/../db.php'; }
$pageTitle = $pageTitle ?? 'BAAM – Sneaker Marketplace';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
</head>
<body>

  <!-- HEADER / NAVIGATION (auf jeder Seite identisch) -->
  <header id="site-header">
    <div class="container">
      <a href="index.php" class="logo"><img src="assets/images/logo.svg" alt="BAAM"></a>
      <nav class="main-nav" aria-label="Hauptnavigation">
        <a href="index.php">Home</a>
        <a href="sortiment.php">Sortiment</a>
        <a href="promotionen.php">Promotionen</a>
        <a href="ueber-uns.php">Über uns</a>
        <a href="verkaufen.php" class="nav-cta">Verkaufen</a>
      </nav>
      <button class="hamburger" id="hamburger" aria-label="Menü öffnen" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <nav id="mobile-nav" aria-label="Mobile Navigation">
    <a href="index.php">Home</a>
    <a href="sortiment.php">Sortiment</a>
    <a href="promotionen.php">Promotionen</a>
    <a href="ueber-uns.php">Über uns</a>
    <a href="verkaufen.php">Verkaufen</a>
  </nav>
