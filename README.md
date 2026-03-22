# FitLife Milano — Backend

## 1. Introduzione

**FitLife Milano (backend)** è l’applicazione Laravel che espone **area riservata** (login, dashboard admin/coach/client) e **API** consumate dal frontend. Il **sito pubblico** (home, corsi, chi siamo, contatti) è gestito dal progetto **FitLifeMilano-frontend**; questo backend espone solo la pagina di **login** come parte pubblica, tutto il resto è protetto da autenticazione e ruoli (Admin, Coach, Cliente). Funzionalità: gestione corsi, utenti, messaggi da contatti, chat in tempo reale (Pusher), prenotazioni corsi, profilo utente e foto profilo.

**Versione attuale:** 1.7.0 (vedi anche `CHANGELOG.md` e `config('app.version')` / `APP_VERSION` in `.env`)

**Documentazione aggiuntiva (cartella `docs/`):**

| File | Contenuto |
|------|-----------|
| [docs/documentazione-progetto.md](docs/documentazione-progetto.md) | Architettura, modelli, viste, flussi (aggiornato con occorrenze e enrollments) |
| [docs/rotte-mappatura-monolite.md](docs/rotte-mappatura-monolite.md) | Mappatura rotte pubbliche vs area riservata |
| [docs/piano-backend-fitlifemilano.md](docs/piano-backend-fitlifemilano.md) | Piano separazione frontend/backend e rotte |
| [CHANGELOG.md](CHANGELOG.md) | Note di rilascio per versione |

### Avvio rapido

- Clonare il repository (o averlo già in locale).
- `composer install`
- Copiare l’ambiente: `copy .env.example .env` (Windows) o `cp .env.example .env` (Linux/macOS).
- `php artisan key:generate`
- Configurare `.env` (database, utente e password del DB).
- `php artisan migrate`
- `php artisan serve` e aprire l’URL indicato nel terminale.

### Produzione e performance

**Deploy:** il backend è deployabile su Render (es. **https://fitlifemilano-backend.onrender.com**). Il sito pubblico è su frontend (es. FitLifeMilano-frontend); il link "Area Riservata" dal sito punta a questo backend.

**Variabili `.env` obbligatorie:**
- `APP_DEBUG=false`
- `APP_ENV=production`
- `SESSION_DRIVER=file` (più veloce; usare `database` o `redis` solo per multi-server)
- `CACHE_STORE=file` (più veloce; `redis` in produzione se disponibile)
- `QUEUE_CONNECTION=database` (broadcast messaggi in coda)

**Queue worker (obbligatorio per chat in tempo reale):**
```bash
php artisan queue:work --queue=broadcasts,default
```
In produzione usare Supervisor per tenere il worker attivo. Configurazione di esempio in `config/supervisor-fitlife.conf`.

**Immagini default foto profilo:** in `public/images/` devono essere presenti `foto-profilo-default-media.jpg` e `foto-profilo-default-piccola.jpg` (placeholder per utenti senza foto). Sono incluse nel repository.

**Checklist pre-deploy:**
1. `APP_DEBUG=false`, `APP_ENV=production`, `APP_KEY` impostato in `.env` (opz. `APP_VERSION` allineato al rilascio)
2. Migrazioni eseguite (`php artisan migrate --force`, già nell'entrypoint Docker) — **obbligatorio** dopo aggiornamenti che aggiungono tabelle (`course_enrollments`, `course_occurrence_settings`, ecc.)
3. Cache artefact dopo il deploy (`php deploy-cache.php`)
4. Immagini default in `public/images/` (foto-profilo-default-*.jpg)
5. Queue worker attivo (Supervisor o equivalente)

**Cache artefact (eseguire dopo ogni deploy o modifica a config/route/view):**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Oppure usare lo script: `php deploy-cache.php` (o `./deploy-cache.sh` su Linux/macOS).

### Repository e sviluppo

- **Repository:** [https://github.com/gioelecavallo13/FitLifeMilano-backend.git](https://github.com/gioelecavallo13/FitLifeMilano-backend.git)
- **Branch principale:** `main` (storico: `master`)
- **Workflow:** lavorare su `main` (o su un branch), poi `git add`, `git commit`, `git push origin main` per pubblicare. Se il remoto è aggiornato da altri: `git pull origin main` (o `git pull --rebase origin main`) prima del push.

---

## 2. Stack e dipendenze

- **Backend:** Laravel 12, PHP 8.2+
- **Viste area riservata:** Bootstrap 5.3, Blade, CSS in `public/css/style.css`, JS in `public/js/`
- **Chat realtime:** Pusher, Laravel Broadcasting, Laravel Echo (canali privati)
- **Profilo:** Intervention Image per elaborazione foto profilo (salvate come BLOB nel DB)
- **API:** rotte in `routes/api.php` (es. GET `/api/health`), CORS configurato per il frontend
- **Debug (opzionale):** Laravel Telescope
- **Asset:** `asset()` su file in `public/`; immagini in `public/images/`

---

## 3. Architettura e ruoli

- **Modelli:** `User` (ruoli: admin, coach, client), `Course`, `ContactRequest`. Relazioni: Course → User (coach), User ↔ Course (prenotazioni many-to-many).
- **Middleware:** `auth` per le aree riservate, `role:admin|coach|client` per separare le dashboard.
- **Flusso login:** `/area-riservata` → POST `/login-process` → redirect a `/dashboard-selector` → in base a `user->role` redirect a `admin.dashboard`, `coach.dashboard` o `client.dashboard`.

---

## 4. Rotte (sintesi)

In questo backend **non** sono presenti le pagine pubbliche del sito (home, corsi, chi siamo, contatti): sono sul frontend. Qui restano solo login (pubblico) e area riservata.

| Tipo        | Esempi |
|------------|--------|
| Pubbliche  | GET `/` → redirect a `/area-riservata`; GET `/area-riservata` (login), POST `/login-process` |
| Guest      | GET login, POST login (come sopra) |
| Auth       | POST `/logout`, GET `/dashboard-selector`, `/profilo`, `/profilo/foto`, `/utenti/{user}/foto` |
| Admin      | `/admin/dashboard`, `/admin/calendario-corsi`, `/admin/courses/*` (CRUD, unenroll, **modifica singola occorrenza** `GET/PUT .../occurrence/{YYYY-MM-DD}`), `/admin/messaggi`, `/admin/messaggi/{id}`, `/admin/chat`, `/admin/inserisci-coach`, `/admin/inserisci-clienti`, `/admin/utenti` (index, show, edit, update, destroy, elimina multipli) |
| Coach      | `/coach/dashboard`, `/coach/corsi`, `/coach/calendario-corsi`, `/coach/clienti/{id}`, `/coach/messaggi` (conversazioni) |
| Client     | `/client/dashboard`, `/client/prenota-corsi`, GET `/client/corsi/{id}`, POST `/client/corsi/{courseId}/prenota`, DELETE `/client/corsi/{courseId}/annulla`, `/client/calendario-corsi`, `/client/messaggi` |
| API        | GET `/api/health` (JSON, senza auth) — CORS consentito per frontend |

Le view restituite sono Blade; i nomi view seguono le convenzioni sotto.

---

## 5. Organizzazione delle View (struttura Blade)

La struttura delle view è **fondamentale** per capire come sono costruite le pagine e come aggiungerne di nuove.

### 5.1 Struttura cartelle (`resources/views/`)

```
resources/views/
├── layouts/
│   ├── layout.blade.php   # Layout principale (master)
│   ├── header.blade.php   # Navbar (inclusa nel layout)
│   └── footer.blade.php   # Footer (incluso nel layout)
├── components/
│   ├── hero.blade.php     # Componente Hero riutilizzabile
│   └── breadcrumb.blade.php
├── partials/
│   └── chat-scripts.blade.php   # Echo/Pusher per chat realtime
├── area-riservata.blade.php     # Unica “pubblica” servita dal backend (login)
├── admin/
│   ├── dashboard.blade.php
│   ├── courses/
│   │   ├── create.blade.php
│   │   ├── show.blade.php    # Anagrafica corso con iscritti per data + link override occorrenza
│   │   ├── edit.blade.php
│   │   └── occurrence-edit.blade.php
│   ├── calendar/
│   │   └── index.blade.php   # Calendario admin
│   ├── messages/
│   │   ├── index.blade.php
│   │   └── show-message.blade.php
│   ├── chat/
│   │   └── index.blade.php   # Lista conversazioni e chat admin
│   ├── coaches/
│   │   └── create.blade.php
│   ├── clients/
│   │   └── create.blade.php
│   └── users/
│       ├── index.blade.php
│       ├── show.blade.php    # Anagrafica utente (corsi prenotati / corsi insegnati)
│       └── edit.blade.php
├── coach/
│   ├── dashboard.blade.php
│   ├── courses/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── clients/
│   │   └── show.blade.php
│   └── messages/
│       └── index.blade.php
├── client/
│   ├── dashboard.blade.php
│   ├── booking.blade.php
│   ├── courses/
│   │   └── show.blade.php   # Dettaglio corso: posti, annulla prenotazione
│   └── messages/
│       └── index.blade.php
├── messages/
│   └── chat.blade.php       # Vista chat condivisa (admin/coach/client)
├── profile/
│   └── show.blade.php
├── emails/
│   └── contact-response.blade.php
├── index.blade.php          # Non servita da questo backend (sito sul frontend)
├── corsi.blade.php
├── chi-siamo.blade.php
└── contatti.blade.php
```

- **Backend:** l’unica pagina “pubblica” servita è `area-riservata` (login). I file `index`, `corsi`, `chi-siamo`, `contatti` esistono ma **non sono raggiungibili** da questo progetto (sito pubblico sul frontend).
- **Pagine per ruolo:** sottocartelle `admin/`, `coach/`, `client/` (incluse `admin/chat`, `coach/messages`, `client/messages`).
- **Layout condiviso:** le pagine dell’area riservata estendono `layouts.layout` e usano header/footer inclusi da lì.

### 5.2 Layout principale (`layouts/layout.blade.php`)

- **Struttura:** `@include('layouts.header')` → `<main>@yield('content')</main>` → `@include('layouts.footer')`.
- **Title:** `@yield('title', 'FitLife')` — ogni pagina può definire `@section('title', 'Titolo | ' . config('app.name'))`.
- **CSS aggiuntivi:** `@stack('styles')` in `<head>` — le pagine usano `@push('styles')` per CSS inline o extra.
- **JS aggiuntivi:** `@stack('scripts')` prima di `</body>` — le pagine usano `@push('scripts')` per script (es. `index.js`).
- **Asset globali:** Bootstrap 5.3 (CDN) e `asset('css/style.css')`; nessun `@vite` nel layout.

**Convenzione per una nuova pagina:**

1. Estendere il layout: `@extends('layouts.layout')`.
2. Impostare il titolo: `@section('title', 'Nome Pagina | ' . config('app.name'))`.
3. Mettere il corpo in `@section('content')` … `@endsection`.
4. Se servono CSS/JS solo per quella pagina: `@push('styles')` / `@push('scripts')` e chiudere con `@endpush`.

### 5.3 Header e Footer

- **Header (`layouts/header.blade.php`):** Navbar Bootstrap scura con logo (route `home`), link a Corsi, Chi Siamo, Contatti. Per ospiti: pulsante "Area Riservata" (`route('login')`). Per utenti autenticati: dropdown "Ciao, {{ Auth::user()->first_name }}" con link Dashboard (`route('dashboard.selector')`) e form logout (POST `route('logout')`).
- **Footer (`layouts/footer.blade.php`):** Logo, link utili (home, corsi, chi siamo, contatti, area riservata), indirizzo e social. Stile coerente (scuro, accenti warning). Nessuna `@section`, solo HTML incluso.

Per nuove voci di menu va modificato solo l'header (e eventualmente il footer se si vogliono gli stessi link).

### 5.4 Componente Hero (`components/hero.blade.php`)

- **Uso:** `<x-hero />` con attributi.
- **Attributi:** `imagePath`, `imageName` (senza estensione), `title`, `subtitle`, opzionali `buttonText`, `buttonUrl`, `alt`.
- **Comportamento:** sezione hero con immagine (WebP + JPG), overlay scuro, titolo/sottotitolo e bottone opzionale. Gli stili hero sono in `style.css` e in parte sovrascritti in `index.blade.php` (es. stats, testimonial).
- **Dove si usa:** `corsi`, `chi-siamo`, `contatti` usano il componente; la **home** ha una hero custom inline (stesso markup ma senza componente) per contenuti e stili specifici.

Per una **nuova pagina con hero:** creare la view che estende `layouts.layout`, definire `@section('content')` e inserire subito `<x-hero imagePath="images/nome-sezione/" imageName="nome-file" title="..." subtitle="..." />`, assicurando che in `public/images/nome-sezione/` ci siano `nome-file.webp` e `nome-file.jpg`.

### 5.5 Pagine pubbliche — pattern comune

- **Layout:** tutte `@extends('layouts.layout')`, `@section('title', ...)`, `@section('content')`.
- **Home (`index.blade.php`):** banner statistiche, hero custom, sezione testimonial; `@push('styles')` per hero/testimonial/stats, `@push('scripts')` per `asset('js/index.js')`.
- **Corsi, Chi siamo, Contatti:** dopo il titolo/sezione iniziale usano `<x-hero ... />` e poi una o più `<section class="...">` con container e griglia Bootstrap. **Corsi:** card statiche (per ora non legate al DB); **Chi siamo:** lista valori + `@foreach` su array `$staff` in Blade; **Contatti:** form (POST `contact.store`) + mappa, gestione `@error`, `old()`, `session('success')`.
- **Area riservata (`area-riservata.blade.php`):** layout full-screen con immagine di sfondo, overlay e form login centrato (POST a `login.process`), senza componente hero.

Per **nuove pagine pubbliche:** creare un file nella root di `views/` (es. `nuova-pagina.blade.php`), estendere il layout, usare eventualmente `<x-hero />` e sezioni con `container`/`row`/`col-*`; aggiungere la rotta in `web.php` che fa `return view('nuova-pagina')` (o con dati da controller).

### 5.6 View area Admin

- **Convenzione cartelle:** una sottocartella per "risorsa" (courses, messages, coaches, clients, users), file `create`, `edit`, `index`, `show` dove servono.
- **Stile comune:** `container py-5`, titolo in alto, pulsante "Torna alla Dashboard" (o "Indietro"), card Bootstrap scure (`bg-dark`, bordi colorati per sezione: primary per corsi, warning per messaggi, ecc.), tabelle `table-dark` con azioni (Modifica/Elimina).
- **Form:** sempre `@csrf`, `@error`/`invalid-feedback`, `old()` per edit; per eliminazione form con `@method('DELETE')` e spesso `onsubmit="return confirm(...)"`.
- **Dashboard admin:** card con link alle varie sezioni (Messaggi, Lista utenti, Inserisci clienti, Inserisci coach, Corsi).
- **Corsi:** `courses/create` e `courses/edit` = form con campi **ripetibilità** (sezione visibile solo se “Ripetibile”); `courses/show` = anagrafica corso, date lezione, override per singola data, iscritti per occorrenza; `courses/occurrence-edit` = form override (orari e scadenze) per una data; **Calendario** (`admin/calendar`) = vista mensile.
- **Messaggi:** `messages/index` = filtri (email, stato) + tabella; `messages/show-message` = dettaglio messaggio + form risposta (che invia email con view `emails/contact-response`).
- **Coach/Clienti:** `coaches/create` e `clients/create` = form registrazione a sinistra + tabella anagrafica a destra; azioni "Modifica" portano a `admin.users.edit`.
- **Utenti:** `users/index` = filtri (search, role) + tabella; `users/show` = anagrafica utente: dettaglio utente + per clienti "corsi prenotati", per coach "corsi insegnati", con link alle schede corso; `users/edit` = form modifica (nome, cognome, email, ruolo).

Per una **nuova sezione admin:** creare la sottocartella in `views/admin/` (es. `admin/nuova-risorsa/`), `index.blade.php` e eventuali `create.blade.php`, `edit.blade.php`, seguendo lo stesso pattern (stesso layout, stessi stili card/tabella, stessi pattern form).

### 5.7 View Coach e Client

- **Coach:** `coach/dashboard.blade.php`, `coach/courses/*`, `coach/clients/show`, `coach/messages/index` (lista conversazioni e chat); stesso layout e stessi stack del resto del sito.
- **Client:** `client/dashboard.blade.php` = card "Prenota corso" + tabella "Le mie prenotazioni" (per data di occorrenza); `client/booking.blade.php` = griglia di card con `data-occurrence-meta` (JSON per giorno: orari lezione effettivi, scadenze prenotazione/annullamento, posti); select data per corsi ripetibili aggiorna header e testi via JS; `client/courses/show.blade.php` = dettaglio con `occurrence_date` in query string; `client/messages/index.blade.php` = chat.

Variabili attese: da controller passare `$courses` per la booking e `$myCourses` per la dashboard cliente.

### 5.8 Email

- **View:** `emails/contact-response.blade.php` — HTML standalone (nessun `@extends`), usata per l'invio della risposta al contatto. Variabili tipiche: `$first_name`, `$subject`, `$replyText` (e altre eventuali usate dal Mailable).

---

## 6. Come implementare una nuova pagina (checklist)

1. **Decidere dove vive la view:** root di `views/` (pubblica), `admin/`, `coach/` o `client/` (area riservata).
2. **Creare il file Blade:** es. `resources/views/nome-pagina.blade.php` o `resources/views/admin/sezione/nome.blade.php`.
3. **Layout:** iniziare con:
   - `@extends('layouts.layout')`
   - `@section('title', 'Titolo Pagina | ' . config('app.name'))`
   - `@section('content')` … contenuto … `@endsection`
4. **Contenuto:** usare `<main>` già nel layout; dentro `@section('content')` usare `<div class="container">` e griglie Bootstrap; per pagine con hero usare `<x-hero ... />` con `imagePath` e `imageName` coerenti con `public/images/`.
5. **Stili/script solo per questa pagina:** `@push('styles')` e `@push('scripts')` (con `@endpush`).
6. **Rotta:** in `routes/web.php` aggiungere la rotta (GET/POST) e, se serve, il metodo nel controller che passa eventuali variabili e fa `return view('nome-view', compact('variabile'))`.
7. **Menu:** se la pagina deve apparire in navbar o footer, aggiornare `layouts/header.blade.php` (e opzionalmente `layouts/footer.blade.php`).
8. **Asset:** immagini in `public/images/` (preferibilmente WebP + fallback); CSS globale in `public/css/style.css`, JS in `public/js/` e incluso con `@push('scripts')` se necessario.

---

## 7. Asset statici (CSS, JS, immagini)

- **CSS:** `public/css/style.css` (hero, card-corso, form, footer, ecc.) incluso dal layout; stili pagina-specifici in `@push('styles')` nelle view.
- **JS:** Bootstrap da CDN nel layout; script per pagina (es. counter/stats in home) in `public/js/index.js` incluso con `@push('scripts')` in `index.blade.php`.
- **Immagini:** in `public/images/` con sottocartelle per sezione: `index/`, `corsi/`, `chi-siamo/`, `contatti/`, `area-riservata/`, più `logo_white.png` (e `.webp`) in root. Convenzione: stesso nome con estensioni `.webp` e `.jpg` (o `.png`) per hero e card.

---

## 8. Riepilogo convenzioni View

| Elemento        | Convenzione |
|-----------------|-------------|
| Layout          | Tutte le pagine web estendono `layouts.layout`. |
| Titolo          | `@section('title', '... \| ' . config('app.name'))`. |
| Contenuto       | `@section('content')` con HTML dentro `<main>`. |
| CSS/JS extra    | `@push('styles')` / `@push('scripts')`. |
| Hero            | Componente `<x-hero />` con `imagePath`, `imageName`, `title`, `subtitle`. |
| Form            | `@csrf`, `@error`, `old()`, route nome in `action`. |
| Admin           | Sottocartelle per risorsa; card scure, tabelle, pulsante "Torna alla Dashboard". |
| Naming view     | Snake_case o kebab per file; cartelle in minuscolo (admin, coach, client, emails). |

---

## Storia delle versioni

Le versioni seguono il [Semantic Versioning](https://semver.org/). Di seguito l’elenco delle versioni con il messaggio di commit associato (più recente in alto).

| Versione | Descrizione |
|----------|-------------|
| 1.7.0 | Corsi ripetibili con **override per occorrenza** (`course_occurrence_settings`); iscrizioni per **`occurrence_date`** (`course_enrollments`); deadline come orari nel giorno della lezione; calendario admin/coach/client; form create/edit condizionale per ripetibili; pagina prenotazione client con meta per data (orari e scadenze dinamici); documentazione e `CHANGELOG.md` |
| 1.6.1 | Cancellazione multipla utenti in admin, layout dashboard a 3 card per riga, miglioramenti UX tabella utenti e prenotazione corsi lato client (card cliccabili, pulsante di prenotazione in anagrafica) |
| 1.5.1 | Aggiornamenti documentazione, .env.example e piano backend |
| 1.5.0 | Prestazioni desktop (CLS, preload LCP, cache asset, dimensioni immagini); fix proporzioni logo navbar |
| 1.4.0 | Fix deploy Render: opzioni SSL DB condizionali (DB_SSL_CA) e mariadb-dev nel Dockerfile |
| 1.3.0 | Anagrafica corsi e utenti, vista corso cliente, posti e annulla prenotazione; README con avvio rapido e repository |
| 1.2.0 | Aggiunto sistema di prenotazione ai corsi |
| 1.1.0 | Completamento sezione admin |
| 1.0.0 | Inserimento gestione e modifica corsi, e gestione e visualizzazione utenti (versione iniziale) |

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
