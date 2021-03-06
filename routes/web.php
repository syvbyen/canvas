<?php

use Canvas\Http\Controllers\Auth\AuthenticatedSessionController;
use Canvas\Http\Controllers\Auth\NewPasswordController;
use Canvas\Http\Controllers\Auth\PasswordResetLinkController;
use Canvas\Http\Controllers\HomeController;
use Canvas\Http\Controllers\PostController;
use Canvas\Http\Controllers\SearchController;
use Canvas\Http\Controllers\StatsController;
use Canvas\Http\Controllers\TagController;
use Canvas\Http\Controllers\TopicController;
use Canvas\Http\Controllers\UploadsController;
use Canvas\Http\Controllers\UserController;
use Canvas\Http\Middleware\AdminMiddleware;
use Canvas\Http\Middleware\AuthenticatedMiddleware;
use Illuminate\Support\Facades\Route;

// Authentication routes...
Route::namespace('Auth')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
         ->name('canvas.login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
         ->name('canvas.password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
         ->name('canvas.password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
         ->name('canvas.password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
         ->name('canvas.password.update');

    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])
         ->name('canvas.logout');
});

// API routes...
Route::middleware([AuthenticatedMiddleware::class])->group(function () {
    Route::prefix('api')->group(function () {
        Route::prefix('uploads')->group(function () {
            Route::post('/', [UploadsController::class, 'store']);
            Route::delete('/', [UploadsController::class, 'destroy']);
        });

        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'index']);
            Route::get('create', [PostController::class, 'create']);
            Route::get('{id}', [PostController::class, 'show']);
            Route::post('{id}', [PostController::class, 'store']);
            Route::delete('{id}', [PostController::class, 'destroy']);
        });

        Route::prefix('stats')->group(function () {
            Route::get('/', [StatsController::class, 'index']);
            Route::get('{id}', [StatsController::class, 'show']);
        });

        Route::prefix('tags')->middleware([AdminMiddleware::class])->group(function () {
            Route::get('/', [TagController::class, 'index']);
            Route::get('create', [TagController::class, 'create']);
            Route::get('{id}', [TagController::class, 'show']);
            Route::get('{id}/posts', [TagController::class, 'showPosts']);
            Route::post('{id}', [TagController::class, 'store']);
            Route::delete('{id}', [TagController::class, 'destroy']);
        });

        Route::prefix('topics')->middleware([AdminMiddleware::class])->group(function () {
            Route::get('/', [TopicController::class, 'index']);
            Route::get('create', [TopicController::class, 'create']);
            Route::get('{id}', [TopicController::class, 'show']);
            Route::get('{id}/posts', [TopicController::class, 'showPosts']);
            Route::post('{id}', [TopicController::class, 'store']);
            Route::delete('{id}', [TopicController::class, 'destroy']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware([AdminMiddleware::class]);
            Route::get('create', [UserController::class, 'create'])->middleware([AdminMiddleware::class]);
            Route::get('{id}', [UserController::class, 'show']);
            Route::get('{id}/posts', [UserController::class, 'showPosts']);
            Route::post('{id}', [UserController::class, 'store']);
            Route::delete('{id}', [UserController::class, 'destroy'])->middleware([AdminMiddleware::class]);
        });

        Route::prefix('search')->group(function () {
            Route::get('posts', [SearchController::class, 'showPosts']);
            Route::get('tags', [SearchController::class, 'showTags'])->middleware([AdminMiddleware::class]);
            Route::get('topics', [SearchController::class, 'showTopics'])->middleware([AdminMiddleware::class]);
            Route::get('users', [SearchController::class, 'showUsers'])->middleware([AdminMiddleware::class]);
        });
    });

    // Catch-all route...
    Route::get('/{view?}', [HomeController::class, 'index'])
         ->where('view', '(.*)')
         ->name('canvas');
});
