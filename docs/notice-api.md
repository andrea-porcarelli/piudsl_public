# API Notices — Specifiche per il Backend

Il frontend pubblico di PiùDSL esegue un polling ogni 60 secondi sull'endpoint
`GET /notices/active` per verificare se c'è un avviso da mostrare agli utenti.

---

## Endpoint richiesto

### GET /api/v1/notices/active

Restituisce l'avviso attivo in questo momento, oppure `null` se non ce n'è nessuno.

**Autenticazione:** header `X-Api-Token` (stesso token S2S già usato dagli altri endpoint).

---

### Risposta — nessun avviso attivo

```json
{
  "data": null
}
```

### Risposta — avviso attivo

```json
{
  "data": {
    "message":    "Manutenzione programmata domenica 6 aprile dalle 02:00 alle 06:00.",
    "type":       "warning",
    "expires_at": "2026-04-06T06:00:00Z"
  }
}
```

---

## Campi di `data`

| Campo        | Tipo            | Obbligatorio | Descrizione |
|-------------|-----------------|:------------:|-------------|
| `message`   | string          | sì           | Testo dell'avviso (max 500 caratteri). |
| `type`      | string          | sì           | `warning` · `danger` · `info` (vedi sotto). |
| `expires_at`| string ISO 8601 | no           | Se presente e nel passato, il frontend ignora l'avviso. |

---

## Tipi di avviso

| `type`    | Aspetto nel frontend        | Uso consigliato                        |
|-----------|-----------------------------|----------------------------------------|
| `warning` | Box giallo, icona triangolo | Manutenzioni programmate, rallentamenti |
| `danger`  | Box rosso, icona ottagonale | Disservizi attivi, emergenze           |
| `info`    | Box blu brand, icona info   | Comunicazioni generiche                |

---

## Note

- Se `data` è `null` o l'endpoint risponde con un errore, il banner non viene mostrato.
- Il frontend non ha endpoint di scrittura: la gestione del ciclo di vita degli avvisi (creazione, modifica, cancellazione, scadenza) è interamente a carico del backend.
- L'endpoint deve rispondere in meno di 5 secondi, altrimenti la richiesta viene abortita e il banner non viene mostrato.
