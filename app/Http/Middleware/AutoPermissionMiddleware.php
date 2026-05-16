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
            'App\Http\Controllers\Api\BlogCategoryController' => 'manage_blogs',
            'App\Http\Controllers\Api\BlogPostController' => 'manage_blogs',
            'App\Http\Controllers\Api\Admin\CMS\PageController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\CMS\BlockController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\CMS\ElementController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\EditorUploadController' => 'manage_pages',
            'App\Http\Controllers\Api\Admin\CountryController' => 'manage_countries',
            'App\Http\Controllers\Api\Admin\Education\UniversityController' => 'manage_education',
            'App\Http\Controllers\Api\Admin\Education\CourseController' => 'manage_education',
            'App\Http\Controllers\Api\Admin\Education\CourseLevelController' => 'manage_education',
            'App\Http\Controllers\Api\Admin\InquiryController' => 'manage_inquiries',
            'App\Http\Controllers\Api\Admin\AppointmentController' => 'manage_bookings',
        ];

        $permission = $controllerPermissions[$controllerClass] ?? null;

        if ($permission && !$user->hasPermission($permission)) {
            return response()->json(['message' => 'Forbidden: Insufficient permissions'], 403);
        }

        return $next($request);
    }
}