# Push Notifications — OneSignal (Area Tecnico)

Documento operativo per il **team che gestisce l'API esterna** (`EXTERNAL_API_BASE_URL`,
quella che possiede `calendar_events`, `users`, ecc.).

L'app Laravel di questo repo è **solo il client**: registra il service worker,
identifica il tecnico via `OneSignal.login(user_id)` e mostra le notifiche. Non
spedisce nulla. L'invio lo fa l'API esterna chiamando direttamente la REST di
OneSignal.

---

## 1. Architettura in breve

```
┌──────────────────────────┐
│  API esterna (backend)   │  ── REST ──▶  OneSignal API
│  - osserva calendar_event│              (api.onesignal.com/notifications)
│  - decide se spedire     │
└──────────────────────────┘                       │
                                                   │ push
                                                   ▼
┌──────────────────────────┐         ┌─────────────────────────────┐
│  Browser / PWA tecnico   │ ◀────── │  OneSignal Web Push          │
│  /tech                   │         │  (gestisce subscription      │
│  OneSignal.login(userId) │         │   con external_id = user_id) │
└──────────────────────────┘         └─────────────────────────────┘
```

Il legame backend ↔ device avviene **esclusivamente tramite `external_id`**, che
deve essere la stringa dello `users.id` del tecnico (lo stesso `user_id`
restituito da `POST /auth/login`). Il client lo imposta con `OneSignal.login()`
appena la dashboard tecnico carica.

---

## 2. Credenziali

| Variabile                  | Dove la prendi                                  | Dove vive             |
|----------------------------|-------------------------------------------------|-----------------------|
| `ONESIGNAL_APP_ID`         | OneSignal → Settings → Keys & IDs → App ID      | env del backend       |
| `ONESIGNAL_REST_API_KEY`   | OneSignal → Settings → Keys & IDs → REST API Key | env del backend (segreta) |

L'App ID è pubblico (lo vede chiunque ispezioni il client). La REST API Key è
**segreta**: non finisce mai nel browser e non si committa.

> Nota: in questo repo (`piudsl_public/.env`) le stesse variabili sono presenti
> ma servono solo come `app_id`/`safari_web_id` lato client. La REST API Key è
> messa per comodità ma **l'app Laravel di questo repo non spedisce push**.

---

## 3. Quando spedire — regole di trigger

Spedisci una push **"Nuovo intervento assegnato"** quando:

1. Un `calendar_event` viene **creato** con `assigned_to` valorizzato.
2. Un `calendar_event` esistente cambia `assigned_to` (riassegnazione).

**NON spedire** quando cambia solo lo stato, la nota o la descrizione: rumore
inutile per il tecnico.

Implementazione consigliata (pseudocodice Laravel):

```php
// app/Observers/CalendarEventObserver.php
public function saved(CalendarEvent $event): void
{
    $isNewAssignment   = $event->wasRecentlyCreated && $event->assigned_to;
    $isReassignment    = $event->wasChanged('assigned_to') && $event->assigned_to;

    if ($isNewAssignment || $isReassignment) {
        SendCalendarEventAssignedPush::dispatch($event->id, $event->assigned_to);
    }
}
```

Il job va in coda (Redis/database queue) così se OneSignal è down o lento la
transazione di salvataggio dell'evento non si rompe.

---

## 4. Payload REST — "Nuovo intervento assegnato"

```http
POST https://api.onesignal.com/notifications
Authorization: Key {ONESIGNAL_REST_API_KEY}
Content-Type: application/json
```

```json
{
  "app_id": "{ONESIGNAL_APP_ID}",
  "target_channel": "push",
  "include_aliases": {
    "external_id": ["42"]
  },
  "headings": { "it": "Nuovo intervento assegnato" },
  "contents": { "it": "Mario Rossi — Via Roma 1 — 25/05 ore 09:00" },
  "url": "https://www.piudsl.it/tech?eventId=15",

  "web_push_topic": "calendar-event-15",
  "ttl": 86400
}
```

Campi importanti:

| Campo | Valore | Note |
|-------|--------|------|
| `target_channel` | `"push"` | evita che venga interpretato come email/SMS |
| `include_aliases.external_id` | array di stringhe | `user_id` del tecnico **come stringa** (cast esplicito, anche se è int sul DB) |
| `headings` / `contents` | oggetti localizzati | `it` è obbligatorio; se in futuro avrai tecnici stranieri aggiungi `en` |
| `url` | deep link | apre `/tech?eventId={id}`. Richiede che la dashboard legga il query param per scrollare/aprire l'evento (follow-up frontend) |
| `web_push_topic` | `calendar-event-{id}` | due notifiche con lo stesso topic si sostituiscono: utile se cambi orario dopo aver già notificato |
| `ttl` | `86400` (24h) | dopo 24h il browser scarta la notifica se non recapitata |

### Risposta tipica

```json
{
  "id": "5dbc7b3e-...",
  "recipients": 1,
  "external_id": null
}
```

`recipients: 0` significa che l'utente **non è subscribed** (non ha mai
installato la PWA o non ha dato il permesso). Non è un errore applicativo, è la
normale condizione iniziale.

---

## 5. Errori e ritentavi

| Status | Causa probabile | Cosa fare |
|--------|----------------|-----------|
| 200    | OK              | Nulla |
| 400    | payload malformato | Logga e investiga (bug nel codice) |
| 401/403 | REST API Key sbagliata | Verifica `.env` del backend |
| 429    | rate limit (irrealistico per il nostro volume) | Backoff esponenziale |
| 5xx    | OneSignal down  | Retry dal job (3 tentativi con backoff) |

Su 5xx, **non bloccare** la transazione applicativa. La push è "best effort":
se non arriva, il tecnico vedrà l'intervento alla prossima apertura della
dashboard (polling normale dell'agenda).

---

## 6. Validazione pre-deploy

1. **Test manuale dal pannello OneSignal**
   - Vai su Messages → New Push.
   - Audience: "Send to particular user" → External ID = il tuo `user_id` di test.
   - Verifica che la notifica arrivi su:
     - Chrome desktop con dashboard aperta in un tab
     - Chrome Android con dashboard installata come PWA
     - Safari iOS 16.4+ **con dashboard "Aggiunta alla schermata Home"** (su iOS senza install non arriva nulla, è un limite del SO)
2. **Test end-to-end**
   - Crea un `calendar_event` lato API assegnato all'utente di test.
   - Verifica nei log del backend: il job è partito, OneSignal ha risposto 200, `recipients: 1`.

---

## 7. Cose che NON serve fare

- ❌ Salvare le subscription Web Push (`endpoint`, `p256dh`, `auth`) nel proprio DB. OneSignal le gestisce.
- ❌ Generare/gestire VAPID keys.
- ❌ Esporre un endpoint per ricevere subscription dal client.
- ❌ Implementare retry manuale del Service Worker, fallback FCM, ecc.

Tutto questo lo astrae OneSignal: tu chiami solo la loro REST.

---

## 8. Follow-up frontend (in questo repo)

Per chiudere il giro user-facing, su `piudsl_public` resta da fare:

1. **Bottone "Abilita notifiche"** nelle impostazioni della dashboard tecnico,
   che chiama `OneSignal.Notifications.requestPermission()` — senza, il
   tecnico non riceverà mai niente.
2. **Onboarding install iOS**: se `navigator.standalone === false` su Safari,
   mostrare istruzioni "Tocca Condividi → Aggiungi alla schermata Home"
   prima di proporre l'attivazione delle push.
3. **Deep link**: leggere `?eventId=` al boot della dashboard e aprire il
   modale evento corrispondente.
