<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($event = $request->event) {
            $query->where('event', $event);
        }

        if ($user_id = $request->user_id) {
            $query->where('user_id', $user_id);
        }

        if ($loggable_type = $request->loggable_type) {
            $query->where('loggable_type', 'App\\Models\\' . $loggable_type);
        }

        if ($from = $request->from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->to) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'from' => $logs->firstItem(),
                'to' => $logs->lastItem(),
            ],
        ]);
    }
}
