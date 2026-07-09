<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get the controller class name
        $route = $request->route();
        if (!$route) {
            return $next($request);
        }

        $controller = $route->getController();
        if (!$controller) {
            return $next($request);
        }

        $controllerClass = get_class($controller);

        // Mapping of controllers to permissions
        $controllerPermissions = [
            // Content — Pages
            'App\Http\Controllers\Api\Admin\CMS\PageController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\CMS\BlockController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\CMS\ElementController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\EditorUploadController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\HeroSliderController' => 'manage_pages',

            // Content — Countries
            'App\Http\Controllers\Api\Admin\CountryController' => 'manage_countries',

            // Content — Team
            'App\Http\Controllers\Api\Admin\TeamMemberController' => 'manage_team_members',

            // Content — Education
            'App\Http\Controllers\Api\Admin\Education\UniversityController' => 'manage_education',
            'App\Http\Controllers\Api\Admin\Education\CourseController' => 'manage_education',
            'App\Http\Controllers\Api\Admin\Education\CourseLevelController' => 'manage_education',
            'App\Http\Controllers\Api\CourseIntakeController' => 'manage_education',

            // Consultancy — Chat
            'App\Http\Controllers\Api\ChatController' => 'manage_chat',

            // Consultancy — Students
            'App\Http\Controllers\Api\Admin\Education\StudentRegistrationController' => 'manage_students',

            // Consultancy — Inquiries & Bookings
            'App\Http\Controllers\Api\Admin\InquiryController' => 'manage_inquiries',
            'App\Http\Controllers\Api\Admin\AppointmentController' => 'manage_bookings',

            // Consultancy — Applications
            'App\Http\Controllers\Api\Admin\Education\ApplicationController' => 'manage_applications',

            // Management — Users
            'App\Http\Controllers\Api\Admin\UserController' => 'manage_users',

            // Management — Activity Log
            'App\Http\Controllers\Api\Admin\ActivityLogController' => 'view_activity_log',

            // Management — Settings
            'App\Http\Controllers\Api\Admin\SettingController' => 'manage_settings',
            'App\Http\Controllers\Admin\ChatSettingController' => 'manage_settings',

            // Management — Footer
            'App\Http\Controllers\Api\Admin\CMS\FooterInfoController' => 'manage_footer',
            'App\Http\Controllers\Api\Admin\CMS\FooterSocialController' => 'manage_footer',

            // Blog
            'App\Http\Controllers\Api\BlogCategoryController' => 'manage_blogs',
            'App\Http\Controllers\Api\BlogPostController' => 'manage_blogs',
        ];

        $permission = $controllerPermissions[$controllerClass] ?? null;

        // Allow GET requests (read-only for lookups) for all authenticated admins and consultants
        if ($request->isMethod('get')) {
            $allowedGetControllers = [
                'App\Http\Controllers\Api\Admin\Education\UniversityController',
                'App\Http\Controllers\Api\Admin\Education\CourseController',
                'App\Http\Controllers\Api\Admin\Education\CourseLevelController',
                'App\Http\Controllers\Api\CourseIntakeController',
                'App\Http\Controllers\Api\Admin\CountryController',
            ];
            if (in_array($controllerClass, $allowedGetControllers)) {
                return $next($request);
            }
        }

        if ($permission && !$user->hasPermission($permission)) {
            return response()->json(['message' => 'Forbidden: Insufficient permissions'], 403);
        }

        return $next($request);
    }
}