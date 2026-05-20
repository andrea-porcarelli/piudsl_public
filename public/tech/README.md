# Service Worker OneSignal — qui

In questa cartella va il file **`OneSignalSDKWorker.js`** scaricato dal wizard di
OneSignal (Settings → Web Configuration → step "Upload Service Worker File").

Il file è referenziato dalla `<head>` di `resources/views/tech/dashboard.blade.php`
con:

```js
serviceWorkerPath:  '/tech/OneSignalSDKWorker.js'
serviceWorkerParam: { scope: '/tech/' }
```

Lo scope `/tech/` fa sì che il service worker venga registrato solo per
l'area tecnico e non per la landing pubblica.

## Aggiornamento

Quando OneSignal pubblica una nuova versione dell'SDK e ti chiede di aggiornare
il worker, **scarica di nuovo il file dallo stesso step del wizard e sostituisci
questo file**: gli utenti già subscribed riceveranno l'update automaticamente
al successivo caricamento della dashboard.

Non rinominare il file, non aggiungere `importScripts` extra: il worker è
self-contained.

## `index.php` in questa cartella — non cancellarlo

Il file `index.php` qui dentro **non** è il service worker: è uno shim che fa da front
controller per `php artisan serve` (usato in produzione). Senza, il PHP built-in web server
vedrebbe questa directory e risponderebbe 404 alla rotta Laravel `GET /tech`. Se
aggiorni il worker, lascia `index.php` dov'è.
