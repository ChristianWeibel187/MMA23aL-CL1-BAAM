# Validierungskonzept – BAAM

Es gibt **zwei** Formulare. Im Browser validieren beide **ausschliesslich mit
JavaScript** (kein HTML5: `novalidate`, kein `required`/`pattern`). Die gemeinsame
Logik steckt in `assets/js/script.js` (Funktion `setupForm`).

Sind alle Felder gültig, verhindert das Skript das Absenden **nicht** mehr,
sondern das Formular wird per `POST` an die zugehörige **PHP-Seite**
(`promotionen.php` bzw. `verkaufen.php`) gesendet. Dort wird **serverseitig
erneut geprüft** (man darf den Browser-Eingaben nie blind vertrauen) und mit
**PDO + Prepared Statements** in die Datenbank geschrieben. Bei Erfolg leitet die
Seite per **Redirect auf `?ok=1`** zurück (PRG-Muster → kein doppeltes Speichern
beim Neuladen) und zeigt die Erfolgsmeldung. Bei einem Fehler werden die Felder
mit den bisherigen Werten neu befüllt und die Fehlermeldungen angezeigt.

---

# 1) Anmeldeformular (Promotion)

**Seite:** `promotionen.html` (rechte Spalte) · **Logik:** `assets/js/script.js`

Die Validierung erfolgt **ausschliesslich mit JavaScript**. Die HTML5-Validierung
ist nicht erlaubt – deshalb hat das `<form>` das Attribut `novalidate` und es
werden keine Attribute wie `required` oder `pattern` verwendet.

## Formularfelder und Validierungsregeln

| # | Feld | Typ | Pflicht | Regel(n) |
|---|------|-----|---------|----------|
| 1 | Vorname | Text | Ja | Nicht leer · min. 2 Zeichen · nur Buchstaben, Leerzeichen und Bindestriche |
| 2 | Nachname | Text | Ja | Nicht leer · min. 2 Zeichen · nur Buchstaben, Leerzeichen und Bindestriche |
| 3 | E-Mail-Adresse | Text | Ja | Nicht leer · gültiges E-Mail-Format (`name@domain.tld`) · max. 100 Zeichen |
| 4 | Schuhgrösse | Select | Ja | Es muss eine Grösse gewählt sein (nicht der Platzhalter `-- Grösse wählen --`) |
| 5 | Lieblingsmarke | Select | Nein | Optional – keine Pflichtvalidierung |
| 6 | AGB akzeptieren | Checkbox | Ja | Muss angehakt sein (`checked === true`) |

## Verwendete Regex

- **Name (Vor-/Nachname):** `^[A-Za-zÀ-ÖØ-öø-ÿ\s\-]+$`
  – erlaubt auch Umlaute/Akzente, Leerzeichen und Bindestriche.
- **E-Mail:** `^[^\s@]+@[^\s@]+\.[^\s@]{2,}$`
  – verlangt einen lokalen Teil, ein `@`, eine Domain und eine Endung mit min. 2 Zeichen.

## Zeitpunkt der Validierung

1. **`blur`** – beim Verlassen eines Feldes wird dieses sofort geprüft.
2. **`input`** – hat ein Feld bereits einen Fehler, wird beim Tippen live neu geprüft.
3. **`submit`** – beim Absenden werden alle Felder erneut geprüft.

## Feedback an den Nutzer

- **Fehler:** rotes Rahmen am Feld (`.error`) + Fehlermeldung unter dem Feld (`.field-error`).
- **Gültig:** grüner Rahmen am Feld (`.valid`).
- **Erfolg:** Nach dem Speichern leitet `promotionen.php` auf `?ok=1` zurück und
  zeigt die grüne Erfolgsmeldung über dem Formular (`#form-success`).
- **Server-Fehler:** Ist die E-Mail bereits vergeben (`UNIQUE`-Verletzung,
  SQLSTATE `23000`), erscheint die Meldung „Diese E-Mail ist bereits
  registriert" direkt am E-Mail-Feld. Fehlt die DB-Verbindung, wird ein
  Hinweis (`.form-error-box`) angezeigt.

DB-Bezug: `INSERT INTO kunde (vorname, nachname, email)` – E-Mail ist `UNIQUE`.
Schuhgrösse und Lieblingsmarke werden im Formular abgefragt, aber (mangels
passender Spalte in `kunde`) **nicht** gespeichert.

---

# 2) Einsende-Formular (Verkaufen)

**Seite:** `verkaufen.html` (rechte Spalte) · **Logik:** `assets/js/script.js`

Über dieses Formular startet ein Mitglied den Einschickungsprozess. Es bildet die
DB-Tabellen `einsendung` und `produkt` ab.

## Formularfelder und Validierungsregeln

| # | Feld | Typ | Pflicht | Regel(n) |
|---|------|-----|---------|----------|
| 1 | Vorname | Text | Ja | Nicht leer · min. 2 Zeichen · nur Buchstaben/Bindestriche |
| 2 | Nachname | Text | Ja | Nicht leer · min. 2 Zeichen · nur Buchstaben/Bindestriche |
| 3 | E-Mail-Adresse | Text | Ja | Nicht leer · gültiges E-Mail-Format · max. 100 Zeichen |
| 4 | Marke | Select | Ja | Es muss eine Marke gewählt sein |
| 5 | Modell | Text | Ja | Nicht leer · min. 2 · max. 100 Zeichen |
| 6 | Schuhgrösse (EU) | Select | Ja | Es muss eine Grösse gewählt sein |
| 7 | Zustand | Select | Ja | Es muss ein Zustand gewählt sein |
| 8 | Wunschpreis (CHF) | Number | Ja | Zahl · grösser als 0 · max. 10000 |
| 9 | Kategorie | Select | Nein | Optional |
| 10 | AGB akzeptieren | Checkbox | Ja | Muss angehakt sein (`checked === true`) |

Zeitpunkt (blur / input / change / submit) und Feedback (rot = `.error`,
grün = `.valid`, Erfolg = `#verkauf-success`) sind identisch zu Formular 1.

DB-Bezug: `verkaufen.php` speichert in **einer Transaktion** über drei Tabellen:

1. **`kunde`** – per E-Mail gesucht; existiert der Kunde schon, wird seine
   `kunde_id` wiederverwendet, sonst `INSERT INTO kunde (vorname, nachname, email)`.
2. **`einsendung`** – `INSERT INTO einsendung (kunde_id, einsende_datum,
   einsende_status)` mit `einsende_datum = CURDATE()` und Status `'eingegangen'`.
3. **`produkt`** – `INSERT INTO produkt (einsendung_id, name, kategorie,
   groesse_eu, preis, marke_id)`. Die Marke wird in `marke` nachgeschlagen bzw.
   neu angelegt (Jordan/Yeezy sind keine eigenen DB-Marken). `grading` bleibt
   zunächst `NULL` – die Zustandsbewertung (1–10) vergibt das BAAM-Team nach der
   Prüfung. Eine leere Kategorie wird auf `'Sneaker'` gesetzt (Spalte ist
   `NOT NULL`). Das Feld **Zustand** dient nur als Hinweis fürs Grading und hat
   keine eigene Spalte.

Schlägt ein Schritt fehl, sorgt `rollBack()` dafür, dass **nichts** gespeichert
wird (keine halben Datensätze).
