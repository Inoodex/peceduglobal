<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Http\Resources\Frontend\TeamMemberResource;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    /**
     * Display a listing of active team members for the frontend.
     */
    public function index()
    {
        $members = TeamMember::where('status', true)->get();
        return TeamMemberResource::collection($members);
    }
}
