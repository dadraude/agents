<?php

use App\AI\Orchestrator\IncidentWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/incidents/process', function (Request $request, IncidentWorkflow $workflow) {
    $text = (string) $request->input('text', '');
    if (trim($text) === '') {
        return response()->json(['error' => 'Missing text'], 422);
    }

    return response()->json($workflow->run($text));
});
