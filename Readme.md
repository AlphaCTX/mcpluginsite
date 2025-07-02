
Schrijf een volledige PHP/MySQL-webapplicatie met AJAX voor dynamische interactie, waarin alleen de beheerder zich kan inloggen en nieuwe Minecraft-plugins kan uploaden. De site heeft de volgende functionaliteiten:

1. **Admin-login**
   - Eén enkele beheerder met vaste gebruikersnaam/wachtwoord (hardcoded of in .env).
   - Sessiebeheer om alleen admin-gebieden te beschermen.

2. **Plugin Upload**
   - AJAX-formulier met progress bar voor bestand (`.jar`), metadata (naam, versie, Minecraft-versie, omschrijving in Markdown).
   - Backend-validatie op bestandstype en grootte.
   - Bestanden opslaan in `uploads/` en metadata in MySQL-tabel `plugins`.

3. **Publieke Pluginlijst & Downloaden**
   - Lijst van alle plugins (naam, versie, beschrijving, download-knop).
   - Wanneer een bezoeker op “Download” klikt, wordt het `.jar`-bestand via PHP geserveerd en wordt er een record toegevoegd aan de MySQL-tabel `downloads`.

4. **Downloadstatistieken**
   - Tellen van downloads per plugin in `downloads` (met timestamp).
   - Admin-pagina met grafiek (bijv. Chart.js) die dagelijkse/wekelijkse totalen toont.

5. **Admin Dashboard**
   - Overzicht van alle plugins met aantal downloads (totaal en per versie).
   - Grafieken en tabellen met AJAX-data.
   - Mogelijkheid om plugins te verwijderen of metadata te bewerken.

6. **Technische eisen**
   - Gebruik PDO voor database-interactie.
   - Scheid presentatie (HTML/JS), logica (PHP) en data (MySQL).
   - AJAX-calls via `fetch()` of `XMLHttpRequest`.
   - Beschrijf in-code comments en zorg voor beveiliging tegen SQL-injectie en XSS.

Geef:
- Alle nodige bestanden (`index.php`, `admin.php`, `upload.php`, `download.php`, `db.php`, eventueel `.htaccess`).
- SQL-scripts voor aanmaken van de tabellen `plugins` en `downloads`.
- Voorbeeld van .env of configbestand.
- Commentaar in de code die uitlegt wat er gebeurt.
