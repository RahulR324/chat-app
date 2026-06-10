<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', [ChatController::class, 'loginPage']);

Route::post('/login', [ChatController::class, 'login']);

Route::get('/chat', [ChatController::class, 'chat']);

Route::post('/send-message', [ChatController::class, 'sendMessage']);

Route::get('/messages/{user}', [ChatController::class, 'loadMessages']);

Route::get('/chat-sidebar', [ChatController::class, 'loadSidebar']);
