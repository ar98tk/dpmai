<?php

use App\Http\Controllers\BusinessAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('business')
    ->group(function (): void {
        Route::post('/login', [BusinessAuthController::class, 'login'])->middleware('guest:business');
        Route::post('/logout', [BusinessAuthController::class, 'logout'])->middleware('auth:business');

        Route::middleware('auth:business')->group(function (): void {
            Route::get('/dashboard', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'business' => $request->user('business'),
                ]);
            });
        });
    });
