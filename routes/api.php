<?php

use App\Http\Controllers\CVConversationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('cv')->group(function () {
    Route::post('/start', [CVConversationController::class, 'start']);
    Route::post('/answer', [CVConversationController::class, 'answer']);
});
