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
    return Auth::check() ? redirect()->route('feed') : Inertia::render('Welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::get('/privacy-policy', fn () => Inertia::render('Legal/PrivacyPolicy'))->name('privacy.policy');
Route::get('/terms', fn () => Inertia::render('Legal/Terms'))->name('terms.conditions');
Route::get('/posts/{post}', [FeedController::class, 'show'])->name('posts.show');
Route::post('/posts/{post}/impression', [FeedController::class, 'impression'])->name('posts.impression');
Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');
Route::get('/in/{user}', [ProfileController::class, 'show'])->name('users.in');

Route::middleware('auth')->group(function () {
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::post('/feed/posts', [FeedController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [FeedController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [FeedController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [PostInteractionController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/share', [PostInteractionController::class, 'share'])->name('posts.share');
    Route::post('/posts/{post}/bookmark', [PostInteractionController::class, 'bookmark'])->name('posts.bookmark');
    Route::post('/posts/{post}/comments', [PostInteractionController::class, 'comment'])->name('comments.store');
    Route::post('/comments/{comment}/like', [PostInteractionController::class, 'likeComment'])->name('comments.like');
    Route::put('/comments/{comment}', [PostInteractionController::class, 'updateComment'])->name('comments.update');
    Route::delete('/comments/{comment}', [PostInteractionController::class, 'deleteComment'])->name('comments.delete');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');
    Route::post('/conversations/{user}', [ChatController::class, 'start'])->name('conversations.start');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages'])->name('conversations.messages');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'send'])->name('messages.store');
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'read'])->name('messages.read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
