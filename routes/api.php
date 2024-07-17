<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\PinToTopController;
use App\Http\Controllers\ChatReactsController;
use App\Http\Controllers\ChatMessagesController;


Route::post('/puhser-edit-user', [PusherController::class, 'edit_user']);
Route::get('user-status/{id}',   [PusherController::class,'user_status']);

Route::middleware(['auth:sanctum', 'verified','localization'])->group(function () {
    //Chat Room
    Route::resource('/Chat-room', ChatRoomController::class);
    Route::post('/Chat-room/accept-request', [ChatRoomController::class,'accept_request']);
    Route::get('/close-chat', [ChatRoomController::class,'close_Chat']);
    Route::resource('/Chat-PinToTop', PinToTopController::class);

    //Chat Message
    Route::resource('/Chat-Message', ChatMessagesController::class);
    Route::post('/delete-Chat-Message', [ChatMessagesController::class,'deleteForAll']);
    Route::post('/delete-Chat-Message-ForMe', [ChatMessagesController::class,'deleteForMe']);
    Route::resource('/Chat-Message-React', ChatReactsController::class);
    Route::post('/find-user', [ChatRoomController::class,'find_user']);
    Route::post('/invite-room', [ChatRoomController::class,'inviteRoom']);
});