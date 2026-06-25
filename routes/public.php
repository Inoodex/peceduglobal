<?php

use App\Http\Controllers\Frontend\FooterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\TeamMemberController as FrontendTeamMemberController;
use App\Http\Controllers\Frontend\UniversityController as FrontendUniversityController;
use App\Http\Controllers\Frontend\CourseController as FrontendCourseController;
use App\Http\Controllers\Frontend\SearchController as FrontendSearchController;
use App\Http\Controllers\Frontend\InquiryController as FrontendInquiryController;
use App\Http\Controllers\Frontend\ConsultantController as FrontendConsultantController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;
use App\Http\Controllers\Frontend\BlogController as FrontendBlogController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\StudentAuthController as FrontendStudentAuthController;
use App\Http\Controllers\Frontend\StudentProfileController as FrontendStudentProfileController;

/*
|--------------------------------------------------------------------------
| Public/Frontend API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register public API routes for your frontend.
| These routes are loaded by bootstrap/app.php with the 'api' middleware group.
|
*/

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
    Route::get('/consultants/slots-by-date', [FrontendConsultantController::class, 'getSlotsByDate']);
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
    Route::get('/pages/country/{countryId}/type/{type}', [FrontendPageController::class, 'getCountryPageByType']);
    Route::get('/pages/university-guide/{universityId}', [FrontendPageController::class, 'getUniversityGuide']);
    Route::get('/popular-destinations', [FrontendPageController::class, 'getPopularDestinations']);
    Route::get('/countries', [FrontendPageController::class, 'getCountriesForNavbar']);
    Route::get('/pages/{slug}', [FrontendPageController::class, 'show']);
    Route::get('/pages/type/{type}', [FrontendPageController::class, 'showByType']);

    // Blog Routes
    Route::get('/blogs', [FrontendBlogController::class, 'index']);
    Route::get('/blogs/{slug}', [FrontendBlogController::class, 'show']);

    // Student Authentication & Profile Management (strictly for public frontend)
    Route::post('/student/login', [FrontendStudentAuthController::class, 'login']);
    Route::post('/student/forgot-password', [FrontendStudentAuthController::class, 'forgotPassword']);
    Route::post('/student/reset-password', [FrontendStudentAuthController::class, 'resetPassword']);
    
    Route::middleware('auth:api')->prefix('student')->group(function () {
        Route::post('logout',           [FrontendStudentAuthController::class, 'logout']);
        Route::get('profile',           [FrontendStudentProfileController::class, 'show']);
        Route::post('profile',          [FrontendStudentProfileController::class, 'update']);
        Route::put('profile/password',  [FrontendStudentProfileController::class, 'updatePassword']);
    });
    //contact
    Route::post('/contact', [ContactController::class, 'index']);
    //footer
    Route::get('/site_info', [FooterController::class, 'index']);

    // Public Chat Routes (For Next.js Frontend)
    Route::prefix('chat')->group(function () {
        Route::get('settings', [\App\Http\Controllers\Api\ChatController::class, 'getPublicSettings']);
        Route::post('init', [\App\Http\Controllers\Api\ChatController::class, 'init']);
        Route::post('send', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
        Route::get('history', [\App\Http\Controllers\Api\ChatController::class, 'getHistory']);
        // Guest marks admin replies as read → flips the admin's ✓→✓✓ in real time.
        Route::post('mark-read', [\App\Http\Controllers\Api\ChatController::class, 'markAdminMessagesRead']);
    });
});
