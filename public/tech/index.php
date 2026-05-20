<?php

// Shim per evitare il 404 del PHP built-in web server (php artisan serve) sulla rotta
// Laravel GET /tech. Senza questo file il built-in server vede la directory
// public/tech/ (che contiene il service worker OneSignal con scope /tech/) e
// restituisce 404 senza mai passare da public/index.php.
//
// Forziamo SCRIPT_NAME / SCRIPT_FILENAME / PHP_SELF come se la richiesta fosse arrivata
// al front controller standard: Laravel/Symfony calcolano il pathInfo come
// REQUEST_URI - SCRIPT_NAME, quindi senza questa sovrascrittura SCRIPT_NAME sarebbe
// '/tech/index.php' e la rotta '/tech' non verrebbe risolta.

$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = realpath(__DIR__ . '/../index.php');
$_SERVER['PHP_SELF']        = '/index.php';

require __DIR__ . '/../index.php';
