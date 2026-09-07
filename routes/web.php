<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('feed') : Inertia::render('Auth/Login');
});

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::middleware('auth')->group(function () {
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::post('/feed/posts', [FeedController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/like', [PostInteractionController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/comments', [PostInteractionController::class, 'comment'])->name('comments.store');
    Route::post('/comments/{comment}/like', [PostInteractionController::class, 'likeComment'])->name('comments.like');
    Route::put('/comments/{comment}', [PostInteractionController::class, 'updateComment'])->name('comments.update');
    Route::delete('/comments/{comment}', [PostInteractionController::class, 'deleteComment'])->name('comments.delete');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');
    Route::post('/conversations/{user}', [ChatController::class, 'start'])->name('conversations.start');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'send'])->name('messages.store');
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'read'])->name('messages.read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
