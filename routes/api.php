<?php

use App\Http\Controllers\Api\Admin\CMS\BlockController;
use App\Http\Controllers\Api\Admin\CMS\ElementController;
use App\Http\Controllers\Api\Admin\CMS\PageController;
use App\Http\Controllers\Api\Admin\CountryController;
use App\Http\Controllers\Api\Admin\Education\UniversityController;
use App\Http\Controllers\Api\Admin\Education\CourseController;
use App\Http\Controllers\Api\Admin\Education\CourseLevelController;
use App\Http\Controllers\Api\Admin\Education\StudentRegistrationController;
use App\Http\Controllers\Api\Admin\Education\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Api\Consultant\AvailabilityController;
use App\Http\Controllers\Api\Admin\HeroSliderController;
use App\Http\Controllers\Api\Student\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogCategoryController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\Admin\EditorUploadController;
use App\Http\Controllers\Api\Student\ApplicationController;
use App\Http\Controllers\Api\Student\ProfileController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\CourseIntakeController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;
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

        // Admin & Shared Management Routes
        Route::middleware(['role:admin,consultant', 'auto-permission'])->prefix('admin')->group(function () {
            // User Management
            Route::get('users', [UserController::class, 'index']);
            Route::put('users/{user}/role', [UserController::class, 'updateRole']);
            Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions']);

            // Permission Management
            Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);

            // Hero Slider Management
            Route::get('hero-sliders', [HeroSliderController::class, 'index']);
            Route::post('hero-sliders', [HeroSliderController::class, 'store']);
            Route::get('hero-sliders/{id}', [HeroSliderController::class, 'show']);
            Route::post('hero-sliders/{id}', [HeroSliderController::class, 'update']);
            Route::delete('hero-sliders/{id}', [HeroSliderController::class, 'destroy']);

            // Country Management
            Route::apiResource('countries', CountryController::class);

            // Education Management
            Route::apiResource('universities', UniversityController::class);
            Route::apiResource('courses', CourseController::class);
            Route::apiResource('course-levels', CourseLevelController::class);
            Route::apiResource('course-intakes', CourseIntakeController::class);

            // Content Management (Pages, Blocks, etc.)
            Route::apiResource('pages', PageController::class);
            Route::apiResource('blocks', BlockController::class);
            Route::post('blocks/reorder', [BlockController::class, 'updateOrder']);
            Route::apiResource('elements', ElementController::class);
            Route::post('editor/upload', [EditorUploadController::class, 'upload']);

            Route::get('students', [StudentRegistrationController::class, 'index']);
            Route::post('students/register', [StudentRegistrationController::class, 'register']);
            Route::post('students/profile', [StudentRegistrationController::class, 'createProfile']);
            Route::get('students/{id}', [StudentRegistrationController::class, 'show']);
            Route::put('students/{id}', [StudentRegistrationController::class, 'update']);
            Route::delete('students/{id}', [StudentRegistrationController::class, 'destroy']);
            Route::delete('students/{id}/document', [StudentRegistrationController::class, 'removeDocument']);

            // Applications Management
            Route::get('applications/metadata', [AdminApplicationController::class, 'metadata']);
            Route::apiResource('applications', AdminApplicationController::class);
        });

        // Booking & Appointment Management (Clean Prefix)
        Route::prefix('booking')->group(function () {

            // Consultant Booking Routes
            Route::middleware('role:consultant')->prefix('consultant')->group(function () {
                Route::post('available-slots', [AvailabilityController::class, 'store']);
                Route::delete('release-slot', [AvailabilityController::class, 'releaseSlot']);
                Route::get('my-claimed-slots', [AvailabilityController::class, 'getMyClaimedSlots']);
                Route::get('student-appointments', [AppointmentController::class, 'getConsultantAppointments']);
            });

            // Student Booking Routes
            Route::middleware('role:student')->prefix('student')->group(function () {
                Route::get('my-appointments', [AppointmentController::class, 'getMyAppointments']);
            });
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

// Frontend api endpoints
Route::prefix('public')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/pages/about', [FrontendPageController::class, 'about']);
    Route::get('/pages/why-choose-us', [FrontendPageController::class, 'whyChooseUs']);
    Route::get('/pages/services', [FrontendPageController::class, 'services']);
    Route::get('/pages/statistics', [FrontendPageController::class, 'statistics']);
    Route::get('/pages/about-the-company', [FrontendPageController::class, 'getAboutCompany']);
    Route::get('/pages/faq', [FrontendPageController::class, 'getFaqs']);
    Route::get('/pages/comparison', [FrontendPageController::class, 'getComparison']);
    Route::get('/pages/country-guide/{countryId}', [FrontendPageController::class, 'getCountryGuide']);
    Route::get('/countries', [FrontendPageController::class, 'getCountriesForNavbar']);

    // Blog Routes
    Route::get('/blogs', [FrontendBlogController::class, 'index']);
    Route::get('/blogs/{slug}', [FrontendBlogController::class, 'show']);
});