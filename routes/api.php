<?php

use App\Http\Controllers\Api\Admin\CMS\BlockController;
use App\Http\Controllers\Api\Admin\CMS\ElementController;
use App\Http\Controllers\Api\Admin\CMS\PageController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\Education\UniversityController;
use App\Http\Controllers\Api\Admin\Education\CourseController;
use App\Http\Controllers\Api\Admin\Education\CourseLevelController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\Admin\EditorUploadController;
use App\Http\Controllers\Api\Student\ApplicationController;
use App\Http\Controllers\Api\Student\ProfileController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\CourseIntakeController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);

        // Blog routes (permission-protected)
        Route::middleware('auto-permission')->group(function () {
            Route::apiResource('blog-categories', BlogCategoryController::class);
            Route::apiResource('blog-posts', BlogPostController::class);
            Route::post('blog-posts/upload-image', [BlogPostController::class, 'uploadImage']);
        });

        // Admin Routes (Protected by role)
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            // User Management
            Route::get('users', [UserController::class, 'index']);
            Route::put('users/{user}/role', [UserController::class, 'updateRole']);
            Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions']);

            // Permission Management
            Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);
        });

        // Admin+Counselor content routes (permission-protected)
        Route::prefix('admin')->middleware(['role:admin,counselor', 'auto-permission'])->group(function () {
            Route::apiResource('pages', PageController::class);
            Route::apiResource('blocks', BlockController::class);
            Route::post('blocks/reorder', [BlockController::class, 'updateOrder']);
            Route::apiResource('elements', ElementController::class);
            Route::post('editor/upload', [EditorUploadController::class, 'upload']);
            Route::apiResource('countries', CountryController::class);
            Route::apiResource('universities', UniversityController::class);
            Route::apiResource('courses', CourseController::class);
            Route::apiResource('course-levels', CourseLevelController::class);
            Route::apiResource('course-intakes', CourseIntakeController::class);
        });

        // Student Routes (Protected by role)
        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('profile', [ProfileController::class, 'show']);
            Route::put('profile', [ProfileController::class, 'update']);
            Route::get('applications', [ApplicationController::class, 'index']);
            Route::post('applications', [ApplicationController::class, 'store']);
            Route::get('applications/{application}', [ApplicationController::class, 'show']);
        });
    });
});

