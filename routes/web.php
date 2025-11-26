<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AiLogController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(
    function () {
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/chat/send', [ChatController::class, 'send']);
        Route::post('/chat/new', [ChatController::class, 'createChat']);
        Route::get('/chat/{chat}', [ChatController::class, 'loadChat']);

        Route::get('/chats', [ChatController::class, 'listChats']);
        Route::delete('/chat/{chat}', [ChatController::class, 'delete'])->name('chat.delete');
    }
);

Route::middleware('auth')->group(
    function () {
        Route::get('/admin', [DocumentController::class, 'dashboard'])->name('admin.dashboard');
        Route::resource('/admin/documents', DocumentController::class);
        Route::get('/admin/ai-logs', [AiLogController::class, 'index'])->name('admin.ai-logs.index');
        Route::get('/admin/ai-logs/{id}', [AiLogController::class, 'show'])->name('admin.ai-logs.show');
    }
);
