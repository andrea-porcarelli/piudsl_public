<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::middleware('technician')->group(function () {
    Route::get('/technician', [TechnicianController::class, 'dashboard']);

    Route::prefix('/api/technician')->group(function () {
        Route::get('/calendar-events', [TechnicianController::class, 'calendarEvents']);
        Route::get('/cart-activities', [TechnicianController::class, 'cartActivities']);
        Route::get('/tickets',         [TechnicianController::class, 'tickets']);
        Route::put('/tickets/{id}',    [TechnicianController::class, 'updateTicket']);
        Route::get('/invoices/paper',            [TechnicianController::class, 'paperInvoices']);
        Route::patch('/invoices/paper/{id}/deliver', [TechnicianController::class, 'deliverPaperInvoice']);
    });
});
