<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/voice-webhook', [WebhookController::class, 'handle'])->name('voice-webhook');
Route::get('/call-options', [WebhookController::class, 'callOptions'])->name('call-options');
Route::get('/handle-recording', [WebhookController::class, 'handleRecording'])->name('handle-recording');
Route::post('/voice-status-webhook', [WebhookController::class, 'handleStatus'])->name('voice-status-webhook');
