<?php

use App\Http\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('support')->name('support.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/{id}', [SupportTicketController::class, 'show'])->name('show');
    Route::post('/{id}/process', [SupportTicketController::class, 'process'])->name('process');
    Route::get('/{id}/agents', [SupportTicketController::class, 'agents'])->name('agents');
});
