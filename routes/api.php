<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook/whatsapp', [App\Http\Controllers\WhatsAppController::class, 'handle']);

Route::post('/webhook/test', function () {
    return response()->json(['ok' => true]);
});
