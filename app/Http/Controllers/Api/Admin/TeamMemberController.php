<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Admin\UpdateTeamMemberRequest;
use App\Http\Resources\Frontend\TeamMemberResource;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeamMemberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = TeamMember::orderBy('id', 'desc');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
        }

        $members = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TeamMemberResource::collection($members)->response()->getData(true)
        ], 200);
    }

    public function store(StoreTeamMemberRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle status boolean
            $data['status'] = filter_var($request->input('status', true), FILTER_VALIDATE_BOOLEAN);

            // Handle image upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('team', $filename, 'public');
                $data['photo'] = 'storage/team/' . $filename;
            }

            $member = TeamMember::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Team member created successfully',
                'data' => new TeamMemberResource($member)
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $member = TeamMember::findOrFail($id);
        return new TeamMemberResource($member);
    }

    public function update(UpdateTeamMemberRequest $request, $id)
    {
        $member = TeamMember::findOrFail($id);

        try {
            $data = $request->validated();

            // Handle status boolean
            if ($request->has('status')) {
                $data['status'] = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN);
            }

            // Handle image upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($member->photo) {
                    $oldPath = str_replace('storage/', '', $member->photo);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $file = $request->file('photo');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('team', $filename, 'public');
                $data['photo'] = 'storage/team/' . $filename;
            } else {
                // If it is not a file, keep the existing one (in case string URL is sent)
                unset($data['photo']);
            }

            $member->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Team member updated successfully',
                'data' => new TeamMemberResource($member)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $member = TeamMember::findOrFail($id);

        try {
            // Delete photo if exists
            if ($member->photo) {
                $oldPath = str_replace('storage/', '', $member->photo);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team member deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
