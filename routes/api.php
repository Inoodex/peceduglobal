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
use App\Http\Controllers\Api\Admin\TeamMemberController;
use App\Http\Controllers\Frontend\TeamMemberController as FrontendTeamMemberController;
use App\Http\Controllers\Frontend\ConsultantController as FrontendConsultantController;
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
use App\Http\Controllers\Frontend\UniversityController as FrontendUniversityController;
use App\Http\Controllers\Frontend\CourseController as FrontendCourseController;
use App\Http\Controllers\Frontend\SearchController as FrontendSearchController;
use App\Http\Controllers\Frontend\InquiryController as FrontendInquiryController;
use App\Http\Controllers\Api\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Api\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Frontend\StudentAuthController as FrontendStudentAuthController;
use App\Http\Controllers\Frontend\StudentProfileController as FrontendStudentProfileController;
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

            // Team Member Management
            Route::get('team-members', [TeamMemberController::class, 'index']);
            Route::post('team-members', [TeamMemberController::class, 'store']);
            Route::get('team-members/{id}', [TeamMemberController::class, 'show']);
            Route::post('team-members/{id}', [TeamMemberController::class, 'update']);
            Route::delete('team-members/{id}', [TeamMemberController::class, 'destroy']);

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
            Route::get('applications/universities', [AdminApplicationController::class, 'getUniversities']);
            Route::get('applications/courses', [AdminApplicationController::class, 'getCourses']);
            Route::get('applications/intakes', [AdminApplicationController::class, 'getIntakes']);
            Route::apiResource('applications', AdminApplicationController::class);

            // Inquiry/Lead Management
            Route::apiResource('inquiries', AdminInquiryController::class)->except(['store']);

            // Booking & Appointment Management
            Route::get('booking-stats', [AdminAppointmentController::class, 'getStats']);
            Route::get('all-schedules', [AdminAppointmentController::class, 'getAllSchedules']);
            Route::get('appointments', [AdminAppointmentController::class, 'index']);
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
            Route::get('applications', [ApplicationController::class, 'index']);
            Route::post('applications', [ApplicationController::class, 'store']);
            Route::get('applications/{application}', [ApplicationController::class, 'show']);
        });
    });
});

// Frontend api endpoints
Route::prefix('public')->group(function () {
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/team-members', [FrontendTeamMemberController::class, 'index']);
    
    // University Routes
    Route::get('/universities', [FrontendUniversityController::class, 'index']);
    Route::get('/universities/{slug}', [FrontendUniversityController::class, 'show']);

    // Course Routes
    Route::get('/courses', [FrontendCourseController::class, 'index']);
    Route::get('/courses/{id}', [FrontendCourseController::class, 'show']);

    // Search Route
    Route::get('/search', [FrontendSearchController::class, 'search']);

    // Inquiry/Lead Submission
    Route::post('/inquiry', [FrontendInquiryController::class, 'store']);

    // Consultant & Booking Public Routes
    Route::get('/consultants/search', [FrontendConsultantController::class, 'search']);
    Route::get('/consultants/available-dates', [FrontendConsultantController::class, 'getAvailableDates']);
    Route::get('/consultants/slots', [FrontendConsultantController::class, 'getSlots']);
    Route::get('/consultants/global-availability', [FrontendConsultantController::class, 'getGlobalAvailability']);
    Route::post('/consultants/book-appointment', [FrontendConsultantController::class, 'bookAppointment']);

    Route::get('/pages', [FrontendPageController::class, 'index']);
    Route::get('/pages/about', [FrontendPageController::class, 'about']);
    Route::get('/pages/about-the-company', [FrontendPageController::class, 'getAboutCompany']);
    Route::get('/pages/why-choose-us', [FrontendPageController::class, 'whyChooseUs']);
    Route::get('/pages/services', [FrontendPageController::class, 'services']);
    Route::get('/pages/statistics', [FrontendPageController::class, 'statistics']);
    Route::get('/pages/faq', [FrontendPageController::class, 'getFaqs']);
    Route::get('/pages/comparison', [FrontendPageController::class, 'getComparison']);
    Route::get('/pages/country-guide/{countryId}', [FrontendPageController::class, 'getCountryGuide']);
    Route::get('/countries', [FrontendPageController::class, 'getCountriesForNavbar']);
    Route::get('/pages/{slug}', [FrontendPageController::class, 'show']);
    Route::get('/pages/type/{type}', [FrontendPageController::class, 'showByType']);

    // Blog Routes
    Route::get('/blogs', [FrontendBlogController::class, 'index']);
    Route::get('/blogs/{slug}', [FrontendBlogController::class, 'show']);

    // Student Authentication & Profile Management (strictly for  public frontend)
    Route::post('/student/login', [FrontendStudentAuthController::class, 'login']);
    
    Route::middleware('auth:api')->prefix('student')->group(function () {
        Route::post('logout',           [FrontendStudentAuthController::class, 'logout']);
        Route::get('profile',           [FrontendStudentProfileController::class, 'show']);
        Route::post('profile',          [FrontendStudentProfileController::class, 'update']);
        Route::put('profile/password',  [FrontendStudentProfileController::class, 'updatePassword']);
    });
});