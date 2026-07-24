<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

// Avvisi pubblici in evidenza (sola lettura — proxy verso API esterna)
Route::get('/api/notice', [NoticeController::class, 'show']);

// Form contatti homepage — invio mail interno (non passa dall'API esterna)
Route::post('/api/contact', [ContactController::class, 'submit']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::middleware('technician')->group(function () {
    Route::get('/tech', [TechnicianController::class, 'dashboard']);

    Route::prefix('/api/technician')->group(function () {
        // Liste
        Route::get('/calendar-events',             [TechnicianController::class, 'calendarEvents']);
        Route::get('/cart-activities',             [TechnicianController::class, 'cartActivities']);
        Route::get('/products',                    [TechnicianController::class, 'products']);

        // Dettaglio + azioni calendario
        Route::get('/calendar-events/{id}',        [TechnicianController::class, 'calendarEventDetail']);
        Route::patch('/calendar-events/{id}',      [TechnicianController::class, 'updateCalendarEvent']);
        Route::post('/calendar-events/{id}/attachments', [TechnicianController::class, 'uploadCalendarAttachment']);

        // Dettaglio + azioni cart activities
        Route::get('/cart-activities/{id}',        [TechnicianController::class, 'cartActivityDetail']);
        Route::patch('/cart-activities/{id}',      [TechnicianController::class, 'updateCartActivity']);
        Route::post('/cart-activities/{id}/plant-coordinates', [TechnicianController::class, 'updateCartActivityPlantCoordinates']);
        Route::post('/cart-activities/{id}/attachments',                      [TechnicianController::class, 'uploadCartActivityAttachment']);
        Route::post('/cart-activities/{id}/extra-products',                   [TechnicianController::class, 'addExtraProduct']);
        Route::delete('/cart-activities/{id}/extra-products/{extraProductId}',[TechnicianController::class, 'removeExtraProduct']);

        // Segnalazioni al backoffice
        Route::post('/reports', [TechnicianController::class, 'createReport']);

        // Fatture
        Route::get('/invoices/paper',                    [TechnicianController::class, 'paperInvoices']);
        Route::patch('/invoices/paper/{id}/deliver',     [TechnicianController::class, 'deliverPaperInvoice']);
    });
});
