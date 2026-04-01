# API Agenda Tecnico — Specifiche per il Backend

Documento di riferimento per tutti gli endpoint necessari alla nuova sezione **Agenda** del dashboard tecnico.

---

## Convenzioni generali

- **Base URL:** `/api/v1`
- **Autenticazione:** ogni richiesta porta due header:
  - `X-Api-Token: <s2s_token>` — token server-to-server fisso
  - `Authorization: Bearer <auth_token>` — token di sessione dell'utente loggato
- **Formato date:** `YYYY-MM-DD`
- **Formato orari:** `HH:MM:SS`
- **Upload file:** `multipart/form-data`; immagini accettate: `jpg`, `jpeg`, `png`, `webp`; dimensione max consigliata: 10 MB per file
- **Risposta standard di successo:**
  ```json
  { "data": { ... } }
  ```
- **Risposta standard di errore:**
  ```json
  { "message": "Descrizione errore" }
  ```

---

## Endpoint esistenti (già implementati)

| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| `GET`  | `/calendar-events` | Lista eventi calendario del tecnico |
| `GET`  | `/cart-activities` | Lista attività/installazioni del tecnico |
| `GET`  | `/tickets` | Lista ticket assegnati al tecnico |
| `PUT`  | `/tickets/{id}` | Aggiorna stato ticket (`ticket_status`) |

---

## Nuovi endpoint richiesti

---

### 1. PRODOTTI

#### GET /products

Restituisce i prodotti filtrabili per tipologia. Usato nel form di aggiunta prodotti extra sulle installazioni.

**Query parameters:**

| Parametro | Tipo | Descrizione |
|-----------|------|-------------|
| `types[]` | array string | Filtra per tipo. Valori: `product`, `supplement` |

**Esempio richiesta:**
```
GET /products?types[]=product&types[]=supplement
```

**Risposta:**
```json
{
  "data": [
    {
      "id": 12,
      "name": "Router WiFi 6",
      "type": "product",
      "price": 49.90,
      "unit": "pz"
    },
    {
      "id": 34,
      "name": "Cavo fibra aggiuntivo (mt)",
      "type": "supplement",
      "price": 1.50,
      "unit": "mt"
    }
  ]
}
```

---

### 2. CALENDAR EVENTS

#### GET /calendar-events/{id}

Dettaglio completo di un singolo evento calendario.

**Risposta:**
```json
{
  "data": {
    "id": 1,
    "title": "Manutenzione rete",
    "status": "open",
    "color": "#0284c7",
    "start_date": "2026-03-31",
    "start_time": "09:00:00",
    "end_date": "2026-03-31",
    "end_time": "12:00:00",
    "customer": "Mario Rossi",
    "description": "Verifica linea principale",
    "notes": [
      {
        "id": 1,
        "body": "Testo della nota",
        "created_by": "Andrea",
        "created_at": "2026-03-31T10:00:00Z"
      }
    ],
    "attachments": [
      {
        "id": 5,
        "url": "https://cdn.example.com/attachments/5.jpg",
        "created_at": "2026-03-31T10:05:00Z"
      }
    ],
    "histories": [
      {
        "note": "Stato cambiato in 'open'",
        "created_at": "2026-03-30T08:00:00Z"
      }
    ]
  }
}
```

---

#### PATCH /calendar-events/{id}

Aggiorna stato e/o aggiunge una nota testuale all'evento.

**Body JSON:**

| Campo | Tipo | Obbligatorio | Descrizione |
|-------|------|:------------:|-------------|
| `status` | string | no | Nuovo stato dell'evento |
| `note` | string | no | Testo della nota da aggiungere (max 2000 caratteri) |

**Valori `status` accettati:** `open`, `in_progress`, `suspended`, `completed`, `close`

**Esempio:**
```json
{
  "status": "in_progress",
  "note": "Iniziata verifica della linea principale."
}
```

**Risposta:** oggetto `data` aggiornato (stesso formato di `GET /calendar-events/{id}`).

---

#### POST /calendar-events/{id}/attachments

Carica una o più immagini sull'evento.

**Content-Type:** `multipart/form-data`

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `images[]` | file | Uno o più file immagine |

**Risposta:**
```json
{
  "data": {
    "attachments": [
      { "id": 6, "url": "https://cdn.example.com/attachments/6.jpg", "created_at": "..." }
    ]
  }
}
```

---

### 3. TICKETS

#### POST /tickets/{id}/notes

Aggiunge una nota testuale al ticket.

**Body JSON:**

| Campo | Tipo | Obbligatorio | Descrizione |
|-------|------|:------------:|-------------|
| `body` | string | sì | Testo della nota (max 2000 caratteri) |

**Risposta:**
```json
{
  "data": {
    "id": 10,
    "body": "Testo della nota",
    "created_by": "Andrea",
    "created_at": "2026-03-31T11:00:00Z"
  }
}
```

---

#### POST /tickets/{id}/attachments

Carica una o più immagini sul ticket.

**Content-Type:** `multipart/form-data`

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `images[]` | file | Uno o più file immagine |

**Risposta:** stessa struttura di `POST /calendar-events/{id}/attachments`.

---

### 4. CART ACTIVITIES (installazioni)

#### GET /cart-activities/{id}

Dettaglio completo di una singola attività/installazione, inclusa l'offerta acquistata dal cliente.

**Risposta:**
```json
{
  "data": {
    "id": 7,
    "customer": "Lucia Bianchi",
    "status": "open",
    "event_at": "2026-03-31",
    "event_time": "14:00:00",
    "full_address": "Via Roma 1, Napoli",
    "coordinates": "40.851775, 14.268124",
    "is_first": true,
    "note": "Accesso dal portone laterale",
    "offer": {
      "name": "PiùDSL 100MB Plus",
      "price": 28.99,
      "description": "Fibra 100MB simmetrica"
    },
    "extra_products": [
      {
        "id": 3,
        "product_id": 12,
        "name": "Router WiFi 6",
        "type": "product",
        "price": 49.90,
        "quantity": 1,
        "subtotal": 49.90
      }
    ],
    "extra_products_total": 49.90,
    "notes": [
      {
        "id": 2,
        "body": "Installazione completata",
        "created_by": "Andrea",
        "created_at": "2026-03-31T15:00:00Z"
      }
    ],
    "attachments": [
      {
        "id": 9,
        "url": "https://cdn.example.com/attachments/9.jpg",
        "created_at": "2026-03-31T15:05:00Z"
      }
    ]
  }
}
```

---

#### PATCH /cart-activities/{id}

Aggiorna stato e/o aggiunge una nota all'attività.

**Body JSON:**

| Campo | Tipo | Obbligatorio | Descrizione |
|-------|------|:------------:|-------------|
| `status` | string | no | Nuovo stato |
| `note` | string | no | Testo nota da aggiungere (max 2000 caratteri) |

**Valori `status` accettati:** `open`, `suspended`, `completed`

---

#### POST /cart-activities/{id}/attachments

Carica una o più immagini sull'attività.

**Content-Type:** `multipart/form-data`

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `images[]` | file | Uno o più file immagine |

---

#### POST /cart-activities/{id}/extra-products

Aggiunge un prodotto extra all'installazione.

**Body JSON:**

| Campo | Tipo | Obbligatorio | Descrizione |
|-------|------|:------------:|-------------|
| `product_id` | integer | sì | ID del prodotto (da `GET /products`) |
| `quantity` | integer | sì | Quantità (min: 1) |

**Risposta:**
```json
{
  "data": {
    "id": 3,
    "product_id": 12,
    "name": "Router WiFi 6",
    "type": "product",
    "price": 49.90,
    "quantity": 1,
    "subtotal": 49.90,
    "extra_products_total": 49.90
  }
}
```

---

#### DELETE /cart-activities/{id}/extra-products/{extra_product_id}

Rimuove un prodotto extra precedentemente aggiunto.

**Risposta:**
```json
{
  "data": {
    "extra_products_total": 0.00
  }
}
```

---

### 5. SEGNALAZIONI

#### POST /reports

Crea una nuova segnalazione per il backoffice. Disponibile in qualsiasi momento dalla sezione Agenda, non legata a una specifica installazione.

**Headers:**
```
X-Api-Token: <s2s_token>
Authorization: Bearer <auth_token>
Content-Type: application/json
```

**Body JSON:**

| Campo | Tipo | Obbligatorio | Descrizione |
|-------|------|:------------:|-------------|
| `date` | string (`YYYY-MM-DD`) | sì | Data della segnalazione (default lato client: oggi) |
| `note` | string | sì | Descrizione della segnalazione (max 2000 caratteri) |

**Esempio richiesta:**
```json
{
  "date": "2026-04-01",
  "note": "Il cliente non era presente all'appuntamento."
}
```

**Risposta (201 Created):**
```json
{
  "data": {
    "id": 42,
    "date": "2026-04-01",
    "note": "Il cliente non era presente all'appuntamento.",
    "technician_id": 5,
    "created_at": "2026-04-01T09:30:00Z"
  }
}
```

**Errori:**

| Codice | Causa |
|--------|-------|
| `422` | Campi mancanti o non validi (`date` formato errato, `note` vuota) |
| `401` | Token di sessione scaduto o non valido |

---

## Riepilogo endpoint

| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| `GET` | `/products?types[]=product&types[]=supplement` | Lista prodotti/supplementi |
| `GET` | `/calendar-events/{id}` | Dettaglio evento calendario |
| `PATCH` | `/calendar-events/{id}` | Aggiorna stato e/o aggiungi nota (chiamata unica) |
| `POST` | `/calendar-events/{id}/attachments` | Carica immagini su evento |
| `PUT` | `/tickets/{id}` | Aggiorna stato ticket (`ticket_status`) |
| `POST` | `/tickets/{id}/notes` | Aggiungi nota a ticket |
| `POST` | `/tickets/{id}/attachments` | Carica immagini su ticket |
| `GET` | `/cart-activities/{id}` | Dettaglio installazione |
| `PATCH` | `/cart-activities/{id}` | Aggiorna stato e/o aggiungi nota (chiamata unica) |
| `POST` | `/cart-activities/{id}/attachments` | Carica immagini su installazione |
| `POST` | `/cart-activities/{id}/extra-products` | Aggiungi prodotto extra |
| `DELETE` | `/cart-activities/{id}/extra-products/{extra_product_id}` | Rimuovi prodotto extra |
| `POST` | `/reports` | Crea segnalazione per il backoffice |

---

## Note per il backend

- Il campo `extra_products_total` deve essere sempre ricalcolato server-side ad ogni aggiunta/rimozione e restituito nella risposta.
- Gli allegati (`attachments`) devono essere accessibili via URL pubblico o URL firmato con scadenza.
- Le note sono **append-only**: non è prevista modifica o cancellazione di note già inserite.
- **Salvataggio unificato:** il frontend invia un unico tasto "Salva" per ogni tipo di attività:
  - **Calendar / Cart Activity:** singola chiamata `PATCH` con `{ status, note }`. Se `note` è assente o vuota non deve creare una nota.
  - **Ticket:** due chiamate sequenziali — `PUT /tickets/{id}` con `{ ticket_status }`, poi (solo se l'operatore ha scritto una nota) `POST /tickets/{id}/notes` con `{ body }`. Gli endpoint devono rispondere in modo indipendente; un errore sulla nota non deve invalidare il cambio stato già salvato.
