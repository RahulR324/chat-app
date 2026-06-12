<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GroupController;


Route::get('/check-notification', [chatController::class, 'checkNotification']);

Route::get('/', [ChatController::class, 'loginPage']);

Route::post('/login', [ChatController::class, 'login']);

Route::get('/chat', [ChatController::class, 'chat']);

Route::post('/send-message', [ChatController::class, 'sendMessage']);

Route::get('/messages/{user}', [ChatController::class, 'loadMessages']);

Route::get('/chat-sidebar', [ChatController::class, 'loadSidebar']);

//groupchat

Route::post('/group/store', [GroupController::class, 'storeGroup']);
Route::get('/group/chat/{id}', [GroupController::class, 'openGroupChat']);
Route::post('/group/send-message', [GroupController::class, 'sendMessage']);
Route::get('/group/messages/{id}', [GroupController::class, 'loadMessages']);
