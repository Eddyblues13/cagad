<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{slug}', [PostController::class, 'show'])->name('posts.show');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');

    // The admin panel in the React site. Every route except login needs a bearer token.
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('login', [Admin\AuthController::class, 'store'])->name('login');

        Route::middleware('admin')->group(function () {
            Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');
            Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

            Route::apiResource('posts', Admin\PostController::class);
            Route::apiResource('categories', Admin\CategoryController::class);
            Route::post('uploads/images', Admin\ImageUploadController::class)->name('uploads.images');

            Route::get('account', [Admin\AccountController::class, 'show'])->name('account.show');
            Route::put('account', [Admin\AccountController::class, 'update'])->name('account.update');

            Route::apiResource('admins', Admin\AdminController::class)
                ->middleware('admin.role:super_admin');
        });
    });
});
