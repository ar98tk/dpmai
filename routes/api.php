<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::post('/webhook/whatsapp', [App\Http\Controllers\WhatsAppController::class, 'handle']);
Route::post('/webhook/whatsapp', [App\Http\Controllers\WebhookController::class, 'handleWithoutInstanceKey']);
Route::post('/webhook/whatsapp/{instance_key}', [App\Http\Controllers\WebhookController::class, 'handle']);
