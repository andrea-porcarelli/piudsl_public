# API Documentation — PiuDSL

Base URL: `https://<dominio>/api/v1`

---

## Autenticazione

Tutte le richieste richiedono il **token S2S** nell'header:

```
X-Api-Token: <service_token>
```

Gli endpoint che operano per conto di un utente richiedono anche il **token utente** (ottenuto via login):

```
Authorization: Bearer <user_token>
```

---

## 1. Auth

### POST `/auth/login`

Esegue il login di un utente (technician o cliente) e restituisce il token utente.

**Middleware:** solo S2S

**Headers:**
```
X-Api-Token: <service_token>
Content-Type: application/json
```

**Request body:**
```json
{
  "email": "utente@esempio.it",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "token": "abc123...64chars",
    "user": {
      "id": 42,
      "name": "Mario Rossi",
      "email": "utente@esempio.it",
      "role": "technician",
      "user_type": null
    }
  }
}
```

**Errori:**
- `401` — credenziali non valide
- `403` — ruolo non consentito (admin / backoffice non possono accedere)
- `422` — validazione email/password mancante

**Nota:** I ruoli consentiti sono `technician` e clienti senza ruolo (`role = null`).

---

### POST `/auth/logout`

Invalida il token utente corrente.

**Middleware:** S2S + User

**Headers:**
```
X-Api-Token: <service_token>
Authorization: Bearer <user_token>
```

**Response 200:**
```json
{
  "success": true,
  "message": "Logout effettuato."
}
```

---

## 2. Tickets

### GET `/tickets`

Restituisce i ticket assegnati all'utente loggato.

- Se `role = technician`: ticket dove `tech_id = utente.id`
- Se cliente (role null): ticket dove `user_id = utente.id`

**Middleware:** S2S + User

**Headers:**
```
X-Api-Token: <service_token>
Authorization: Bearer <user_token>
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ticket_status": "open",
      "ticket_level": "high",
      "department": "technicians",
      "cart_id": 10,
      "user_id": 5,
      "tech_id": 3,
      "technician": "Luca Bianchi",
      "customer": "Mario Rossi",
      "messages_count": 2,
      "created_at": "2026-03-20T10:00:00.000000Z",
      "updated_at": "2026-03-22T08:30:00.000000Z"
    }
  ]
}
```

**Valori `ticket_status`:** `open`, `pending`, `close`
**Valori `ticket_level`:** `normal`, `low`, `high`
**Valori `department`:** `admins`, `technicians`

---

### PUT `/tickets/{id}`

Modifica i campi di un ticket. L'utente può modificare solo i ticket che gli appartengono.

**Middleware:** S2S + User

**Headers:**
```
X-Api-Token: <service_token>
Authorization: Bearer <user_token>
Content-Type: application/json
```

**Request body** (tutti i campi sono opzionali):
```json
{
  "ticket_status": "pending",
  "ticket_level": "high",
  "department": "admins"
}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "ticket_status": "pending",
    "ticket_level": "high",
    "department": "admins"
  }
}
```

**Errori:**
- `403` — ticket non appartiene all'utente
- `404` — ticket non trovato
- `422` — valore non valido per i campi

---

## 3. Cart Activities

### GET `/cart-activities`

Restituisce le attività di intervento assegnate al tecnico loggato.

**Middleware:** S2S + User
**Disponibile solo per:** `role = technician`

**Headers:**
```
X-Api-Token: <service_token>
Authorization: Bearer <user_token>
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 7,
      "cart_id": 10,
      "customer": "Mario Rossi",
      "status": "done",
      "is_first": true,
      "note": "Installazione modem",
      "full_address": "Via Roma 1, 00100 Roma",
      "coordinates": "41.9028,12.4964",
      "event_at": "2026-03-25",
      "event_time": "09:00:00",
      "invoice_id": null,
      "created_at": "2026-03-20T10:00:00.000000Z"
    }
  ]
}
```

**Errori:**
- `403` — l'utente non è un tecnico

---

## 4. Fatture Cartacee

### GET `/invoices/paper?month={month}&year={year}`

Restituisce le fatture dove l'ordine ha `invoice_by_paper = 1`, filtrate per mese e anno.

**Middleware:** solo S2S (nessun token utente richiesto)

**Headers:**
```
X-Api-Token: <service_token>
```

**Query parameters:**

| Parametro | Tipo    | Obbligatorio | Descrizione             |
|-----------|---------|--------------|-------------------------|
| `month`   | integer | Sì           | Mese (1–12)             |
| `year`    | integer | Sì           | Anno (es. 2026)         |

**Esempio:**
```
GET /api/v1/invoices/paper?month=3&year=2026
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 100,
      "invoice_name": "B00A1",
      "invoice_type": "subscription",
      "type_label": "Abbonamento",
      "month": 3,
      "year": 2026,
      "amount": 29.99,
      "total_net": 27.49,
      "discount": 2.50,
      "user_id": 5,
      "customer": "Mario Rossi",
      "cart_id": 10,
      "delivered_at": null,
      "delivered_by": null,
      "notes": null,
      "created_at": "2026-03-01T08:00:00.000000Z"
    }
  ]
}
```

**Errori:**
- `422` — `month` o `year` mancanti o non validi

---

## 5. Calendar Events

### GET `/calendar-events`

Restituisce le attività del calendario assegnate all'utente loggato.

- Se `role = technician`: eventi con `assigned_to = utente.id` AND `department = 'technician'`
- Se cliente (role null): eventi con `user_id = utente.id`

**Middleware:** S2S + User

**Headers:**
```
X-Api-Token: <service_token>
Authorization: Bearer <user_token>
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "title": "Intervento tecnico",
      "description": "Configurazione router",
      "status": "in_progress",
      "status_label": "In lavorazione",
      "department": "technician",
      "department_label": "Tecnici",
      "color": "#f0ad4e",
      "start_date": "2026-03-28",
      "start_time": "10:00:00",
      "end_date": "2026-03-28",
      "end_time": "12:00:00",
      "user_id": 5,
      "customer": "Mario Rossi",
      "cart_id": 10,
      "assigned_to": 3,
      "assignee": "Luca Bianchi",
      "suspension_reason": null,
      "histories": [
        {
          "id": 2,
          "note": "Cliente assente, riprogrammare",
          "created_at": "2026-03-27T14:00:00.000000Z"
        }
      ],
      "created_at": "2026-03-20T09:00:00.000000Z"
    }
  ]
}
```

**Valori `status`:** `open`, `in_progress`, `suspended`, `completed`
**Valori `department`:** `technician`, `backoffice`, `admin`

---

## 6. Prodotti

### GET `/products/published`

Restituisce tutti i prodotti con `publish_on_site = 1`, ordinati per popolarità.

**Middleware:** solo S2S (nessun token utente richiesto)

**Headers:**
```
X-Api-Token: <service_token>
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "label": "Fibra 1Gbps",
      "product_type": "service",
      "offer_title": "Fibra Ultraveloce",
      "short_description": "La connessione più veloce per la tua casa",
      "what_is_included": "Router incluso, assistenza 24/7",
      "public_price": 29.99,
      "is_popular": true,
      "cta_label": "Attiva ora",
      "upload_speed": "300 Mbps",
      "download_speed": "1000 Mbps",
      "pricing": {
        "forfait": null,
        "month": 29.99,
        "months_2": null,
        "months_6": null,
        "year": null
      },
      "tax": {
        "id": 1,
        "tax": 22
      }
    }
  ]
}
```

**Valori `product_type`:** `product`, `service`, `additional`
**Valori `target_audience`:** `private` (Privato), `business` (Azienda), `null` (non specificato)

---

## Codici di errore comuni

| Codice | Significato |
|--------|-------------|
| `401`  | Token S2S mancante o non valido / credenziali utente errate |
| `403`  | Operazione non consentita per questo utente/ruolo |
| `404`  | Risorsa non trovata |
| `422`  | Errore di validazione dei parametri |

**Formato errore:**
```json
{
  "success": false,
  "message": "Descrizione dell'errore"
}
```

---

## Setup iniziale

Per creare un token S2S, usare tinker:

```bash
php artisan tinker
```

```php
\App\Models\ServiceToken::create([
    'name'  => 'NomeServizioEsterno',
    'token' => \Illuminate\Support\Str::random(64),
]);
```

Dopo aver eseguito le migration:

```bash
php artisan migrate
```
