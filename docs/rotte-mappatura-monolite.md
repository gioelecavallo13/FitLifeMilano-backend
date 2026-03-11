# Mappatura rotte monolite FitLifeMilano

Classificazione delle rotte in **pubbliche** vs **area riservata** (admin / coach / client) per la separazione front-end / back-end.

## Rotte pubbliche (sito front-end)

| Metodo | URI | Nome | Destinazione |
|--------|-----|------|--------------|
| GET | `/` | home | view index |
| GET | `/corsi` | corsi | view corsi |
| GET | `/chi-siamo` | chi-siamo | view chi-siamo |
| GET | `/contatti` | contatti | view contatti |
| POST | `/contatti/store` | contact.store | ContactRequestController@store |

## Rotte guest (login – back-end, unica parte “pubblica” del backend)

| Metodo | URI | Nome | Destinazione |
|--------|-----|------|--------------|
| GET | `/area-riservata` | login | view area-riservata |
| POST | `/login-process` | login.process | AuthController@login |

## Rotte protette (auth) – back-end

### Comuni (tutti i ruoli autenticati)

- POST `/logout` (logout)
- GET `/dashboard-selector` (dashboard.selector)
- GET `/profilo` (profile.show)
- POST `/profilo/foto` (profile.updatePhoto)
- GET `/utenti/{user}/foto` (profile.photo)

### Admin (middleware role:admin, prefix admin)

- dashboard, corsi (CRUD, unenroll), messaggi, chat, inserisci-coach, inserisci-clienti, utenti (index, show, edit, update, destroy)

### Coach (middleware role:coach, prefix coach)

- dashboard, corsi, clienti, messaggi (conversazioni)

### Client (middleware role:client, prefix client)

- dashboard, prenota-corsi, corsi (show, enroll, cancel), messaggi (conversazioni)

## Assegnazione dopo la separazione

- **Front-end (FitLifeMilano-frontend):** solo le rotte **pubbliche** (home, corsi, chi-siamo, contatti, contact.store). Link "Area riservata" punta all’URL del back-end.
- **Back-end (FitLifeMilano-backend):** rotte **guest** (login) + tutte le rotte **protette** (auth). Nessuna rotta home/corsi/chi-siamo/contatti.
