<?php

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('support')->name('support.')->group(function () {
    Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    Route::get('/{id}', [SupportTicketController::class, 'show'])->name('show');
    Route::post('/{id}/process', [SupportTicketController::class, 'process'])->name('process');
    Route::post('/{id}/process-stream', [SupportTicketController::class, 'processStream'])->name('processStream');
    Route::post('/{id}/create-linear', [SupportTicketController::class, 'createLinear'])->name('createLinear');
    Route::post('/process-batch', [SupportTicketController::class, 'processBatch'])->name('processBatch');
    Route::post('/process-batch-stream', [SupportTicketController::class, 'processBatchStream'])->name('processBatchStream');
    Route::get('/{id}/agents', [SupportTicketController::class, 'agents'])->name('agents');
});

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::put('/', [SettingsController::class, 'update'])->name('update');
});
