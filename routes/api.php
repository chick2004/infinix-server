<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MediaController;

use Illuminate\Support\Facades\Log;

Route::middleware('guest')->group(function () {

    Route::get('/media/{path}', [MediaController::class, 'media'])->where('path', '.*');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/verify-email', [AuthController::class, 'send_verification_code']);
    Route::post('/verify-code', [AuthController::class, 'verify_code']);

    Route::post('/reset-password', [AuthController::class, 'reset_password']);

    Route::get('/unauthenticate', function (Request $request) {
        return response()->json(['message' => 'Token is invalid'], 401);
    });

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'change_password']);
    
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/users', [AuthController::class, 'list']);
    Route::get('/users/{id}', [AuthController::class, 'show']);

    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::delete('/posts/{id}/force', [PostController::class, 'force_destroy']);
    Route::post('/posts/{id}/restore', [PostController::class, 'restore']);
    Route::get('/users/{id}/posts', [PostController::class, 'by_user']);
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::get('/posts/{id}/comments', [CommentController::class, 'by_post']);
    Route::get('/comments/{id}', [CommentController::class, 'show']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/posts/{id}/bookmark', [PostController::class, 'bookmark']);
    Route::post('/posts/{id}/like', [PostController::class, 'like']);

    
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag}/posts', [PostController::class, 'by_tag']);


    Route::post('/friend-requests', [FriendRequestController::class, 'store']);
    Route::get('/friend-requests/{id}', [FriendRequestController::class, 'show']);
    Route::put('/friend-requests/{id}', [FriendRequestController::class, 'update']);
    Route::delete('/friend-requests/{id}', [FriendRequestController::class, 'destroy']);
    Route::get('users/{id}/friend-requests', [FriendRequestController::class, 'by_user']);

    Route::get('conversations/with_user/{id}', [ConversationController::class,'with_user']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::get('conversations/{id}', [ConversationController::class, 'show']);
    Route::put('conversations/{id}', [ConversationController::class, 'update']);
    Route::delete('conversations/{id}', [ConversationController::class, 'destroy']);
    Route::get('users/{id}/conversations', [ConversationController::class, 'by_user']);
    Route::get('conversations/{id}/users', [ConversationController::class, 'users']);

    Route::post('conversations/{id}/messages', [MessageController::class, 'store']);
    Route::get('conversations/{id}/messages', [MessageController::class, 'by_conversation']);
    Route::get('messages/{id}', [MessageController::class, 'show']);
    Route::put('messages/{id}', [MessageController::class, 'update']);
    Route::delete('messages/{id}', [MessageController::class, 'destroy']);
    Route::delete('messages/{id}/force', [MessageController::class, 'force_destroy']);
    Route::post('messages/{id}/restore', [MessageController::class, 'restore']);
});
