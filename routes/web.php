<?php

use App\Http\Controllers\CVConversationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('cv')->group(function () {
    Route::post('/start', [CVConversationController::class, 'start']);
    Route::post('/answer', [CVConversationController::class, 'answer']);
});
