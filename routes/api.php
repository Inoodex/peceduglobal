<?php

use App\Http\Controllers\Api\Admin\CMS\BlockController;
use App\Http\Controllers\Api\Admin\CMS\ElementController;
use App\Http\Controllers\Api\Admin\CMS\PageController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\Education\UniversityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\Student\ApplicationController;
use App\Http\Controllers\Api\Student\ProfileController;
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
            Route::apiResource('pages', PageController::class);
            Route::apiResource('blocks', BlockController::class);
            Route::apiResource('elements', ElementController::class);
            Route::apiResource('countries', CountryController::class);

            // Education Routes
            Route::apiResource('universities', UniversityController::class);
            // Route::apiResource('scholarships', ScholarshipController::class);
        });

        // Student Routes
        Route::prefix('student')->group(function () {
            Route::get('profile', [ProfileController::class, 'show']);
            Route::put('profile', [ProfileController::class, 'update']);
            Route::get('applications', [ApplicationController::class, 'index']);
            Route::post('applications', [ApplicationController::class, 'store']);
            Route::get('applications/{application}', [ApplicationController::class, 'show']);
        });
    });
});

