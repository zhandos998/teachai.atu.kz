<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/chat/send', [ChatController::class, 'send']);
// Route::middleware('auth')->group(function () {
//     Route::post('/chat/send', [ChatController::class, 'send']);
// });
