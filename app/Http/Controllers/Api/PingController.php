<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PingController extends Controller
{
    public function ping(Request $request)
    {
        $request->user()->update(['last_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
