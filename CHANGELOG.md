# Changelog

Tutte le note di rilascio rilevanti per **FitLife Milano — Backend**. Le versioni seguono [Semantic Versioning](https://semver.org/).

## [1.7.0] — 2026-03-18

### Aggiunto

- Tabella **`course_occurrence_settings`**: override per singola data di lezione (orari, chiusura prenotazioni e annullamenti) rispetto ai default su `courses`.
- Tabella **`course_enrollments`** con migrazione da pivot `course_user`: iscrizioni legate a **`occurrence_date`** (una prenotazione = una data di lezione).
- Modelli: `CourseEnrollment`, `CourseOccurrenceSetting`, `CourseOccurrenceSnapshot`; `Course` con metodi per occorrenze, capacità/deadline per data (`getEffective*ForDate`, `occurrenceDatesBetween`, ecc.).
- **Admin:** form corsi create/edit con campi condizionali se **Ripetibile** (fine ciclo, cutoff disdette client); anagrafica corso con elenco date e modifica singola occorrenza (`occurrence-edit`); link calendario corsi.
- **Admin calendar:** `GET /admin/calendario-corsi` (`admin.calendar`).
- **Coach / Client:** `GET /coach/calendario-corsi` e `GET /client/calendario-corsi` (`CalendarController` + `CalendarService`).
- Comando Artisan `SnapshotCourseOccurrences` per snapshot storici occorrenze.
- **Client booking:** `occurrenceMeta` in JSON per ogni giorno (`lesson_start`, `lesson_end`, `booking_deadline`, `cancel_deadline`, posti, flag); card **Prenota corsi** aggiornata al cambio data (header orari, scadenze, badge data per corsi ripetibili).

### Test

- Feature e unit test su deadline, capacità, override occorrenza, meta pagina prenotazione, calendario, filtri admin.

### Note operative

- Dopo il deploy: `php artisan migrate --force` (crea/aggiorna le nuove tabelle).
- Impostare `APP_VERSION` in `.env` per la versione in footer (nessun default in `config/app.php`).
