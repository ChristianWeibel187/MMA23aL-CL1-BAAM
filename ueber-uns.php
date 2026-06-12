<?php
/* ============================================================
   BAAM – Über uns
   ============================================================ */
require __DIR__ . '/db.php';
$pageTitle = 'BAAM – Über uns';
require __DIR__ . '/partials/header.php';
?>

  <!-- PAGE BANNER -->
  <section class="page-banner">
    <div class="container">
      <span class="eyebrow">BAAM</span>
      <h1>Über uns</h1>
      <p>BAAM ist ein Marktplatz, der Sneaker-Handel transparent und
         vertrauenswürdig macht – aufgebaut als Schulprojekt im Modul M291.</p>
    </div>
  </section>


  <!-- UNSER KONZEPT / WERTE -->
  <section class="about-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Unser Konzept</h2>
      </div>
      <div class="about-grid">
        <div class="about-card">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          </span>
          <h3>Transparenz</h3>
          <p>Jede Transaktion ist nachvollziehbar. Du weisst immer, was du
             kaufst und von wem.</p>
        </div>
        <div class="about-card">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 17.8 6.8 19.6l1-5.8L3.5 9.7l5.9-.9z"/></svg>
          </span>
          <h3>Rating-System</h3>
          <p>Zustand, Qualität und Authentizität werden auf einer Skala von
             1 bis 10 bewertet.</p>
        </div>
        <div class="about-card">
          <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3.2 3.2 0 0 1 0 5.6"/><path d="M17.5 13.5a5.5 5.5 0 0 1 3 5.5"/></svg>
          </span>
          <h3>Community</h3>
          <p>BAAM verbindet Sneaker-Enthusiasten – fair, sicher und ohne
             versteckte Gebühren.</p>
        </div>
      </div>
    </div>
  </section>


  <!-- TEAM -->
  <section class="about-section" style="padding-top:0;">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Das Team</h2>
      </div>
      <div class="team-grid">
        <div class="team-card">
          <div class="team-avatar">N</div>
          <h4>Noah</h4>
          <p>Backend &amp; Datenbank</p>
        </div>
        <div class="team-card">
          <div class="team-avatar">C</div>
          <h4>Christian</h4>
          <p>Front-End &amp; Design</p>
        </div>
        <div class="team-card">
          <div class="team-avatar">L</div>
          <h4>Linton</h4>
          <p>Dokumentation</p>
        </div>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/partials/footer.php'; ?>
