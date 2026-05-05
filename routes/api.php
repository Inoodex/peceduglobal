<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogPostController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        // Blog routes
        Route::apiResource('blog-categories', BlogCategoryController::class);
        Route::apiResource('blog-posts', BlogPostController::class);
        Route::post('blog-posts/upload-image', [BlogPostController::class, 'uploadImage']);

        // Admin CMS Routes
        Route::prefix('admin')->group(function () {
            Route::apiResource('pages', \App\Http\Controllers\Api\Admin\CMS\PageController::class);
            Route::apiResource('blocks', \App\Http\Controllers\Api\Admin\CMS\BlockController::class);
            Route::apiResource('elements', \App\Http\Controllers\Api\Admin\CMS\ElementController::class);
            
            // Education Routes
            Route::apiResource('universities', \App\Http\Controllers\Api\Admin\Education\UniversityController::class);
            Route::apiResource('scholarships', \App\Http\Controllers\Api\Admin\Education\ScholarshipController::class);
        });

        // Student Routes
        Route::prefix('student')->group(function () {
            Route::get('profile', [\App\Http\Controllers\Api\Student\ProfileController::class, 'show']);
            Route::put('profile', [\App\Http\Controllers\Api\Student\ProfileController::class, 'update']);
            Route::get('applications', [\App\Http\Controllers\Api\Student\ApplicationController::class, 'index']);
            Route::post('applications', [\App\Http\Controllers\Api\Student\ApplicationController::class, 'store']);
            Route::get('applications/{application}', [\App\Http\Controllers\Api\Student\ApplicationController::class, 'show']);
        });
    });
});

