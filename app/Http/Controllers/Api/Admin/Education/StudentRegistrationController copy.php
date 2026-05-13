<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\CourseIntake;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StudentRegistrationController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $students = User::where('role', 'student')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $students,
                'message' => 'Students retrieved successfully.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a student user. If country_id is sent, also creates a student profile (dashboard "create student" flow).
     * Legacy: email, password, phone, full_name only — profile optional.
     */
    public function register(Request $request): JsonResponse
    {
        $withProfile = $request->filled('country_id');

        $rules = [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:20',
        ];

        if ($withProfile) {
            $rules = array_merge($rules, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'country_id' => 'required|exists:countries,id',
                'father_name' => 'nullable|string|max:255',
                'mother_name' => 'nullable|string|max:255',
                'sponsor_phone' => 'nullable|string|max:20',
                'passport_number' => 'nullable|string|max:50',
                'passport_validity' => 'nullable|date',
                'address' => 'nullable|string',
                'date_of_birth' => 'nullable|date',
                'university_id' => 'nullable|exists:universities,id',
                'course_id' => 'nullable|exists:courses,id',
                'course_intake_id' => 'nullable|exists:course_intakes,id',
                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'translation_docs' => 'nullable|array',
                'translation_docs.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            ]);
        } else {
            $rules['full_name'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            if ($withProfile) {
                return $this->registerWithProfile($request);
            }

            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'phone' => $request->phone,
                'consultant_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Student account created successfully.',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function registerWithProfile(Request $request): JsonResponse
    {
        try {
            $result = DB::transaction(function () use ($request) {
                $fullName = trim($request->first_name.' '.$request->last_name);

                $user = User::create([
                    'full_name' => $fullName,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'student',
                    'phone' => $request->phone,
                    'consultant_id' => auth()->id(),
                ]);

                $applicationId = 'APP-'.date('Y').'-'.strtoupper(Str::random(6));

                $preferredIntake = null;
                if ($request->filled('course_intake_id')) {
                    $preferredIntake = CourseIntake::whereKey($request->course_intake_id)->value('intake_name');
                }

                $profile = StudentProfile::create([
                    'user_id' => $user->id,
                    // 'application_id' => $applicationId,
                    'phone' => $request->phone,
                    'father_name' => $request->father_name,
                    'mother_name' => $request->mother_name,
                    'date_of_birth' => $request->date_of_birth,
                    'address' => $request->address,
                    'sponsor_phone' => $request->sponsor_phone,
                    'passport_number' => $request->passport_number,
                    'passport_validity' => $request->passport_validity,
                    'country_id' => $request->country_id,
                    'university_id' => $request->university_id,
                    'course_id' => $request->course_id,
                    'course_intake_id' => $request->course_intake_id,
                    'preferred_intake' => $preferredIntake,
                    'documents' => $this->storeUploadedDocuments($request->file('documents')),
                    'translation_documents' => $this->storeUploadedDocuments($request->file('translation_docs')),
                ]);

                return compact('user', 'profile', 'applicationId');
            });

            return response()->json([
                'success' => true,
                'message' => 'Student account and profile created successfully.',
                'application_id' => $result['applicationId'],
                'data' => [
                    'user' => $result['user'],
                    'profile' => $result['profile'],
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'phone' => 'required|string|max:20',
            'country_id' => 'required|exists:countries,id',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'sponsor_phone' => 'nullable|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:50',
            'passport_validity' => 'nullable|date',
            'nationality' => 'nullable|string|max:255',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'course_intake_id' => 'nullable|exists:course_intakes,id',
            'preferred_intake' => 'nullable|string|max:100',
            'cgpa' => 'nullable|numeric|between:0,10',
            'ielts_score' => 'nullable|numeric|between:0,9',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'translation_docs' => 'nullable|array',
            'translation_docs.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $profile = StudentProfile::firstOrNew(['user_id' => $request->user_id]);

            if (! $profile->exists) {
                $profile->application_id = 'APP-'.date('Y').'-'.strtoupper(Str::random(6));
            }

            $preferredIntake = $request->preferred_intake;
            if ($request->filled('course_intake_id')) {
                $preferredIntake = CourseIntake::whereKey($request->course_intake_id)->value('intake_name') ?? $preferredIntake;
            }

            $profile->fill([
                'phone' => $request->phone,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'sponsor_phone' => $request->sponsor_phone,
                'alternative_phone' => $request->alternative_phone,
                'passport_number' => $request->passport_number,
                'passport_validity' => $request->passport_validity,
                'nationality' => $request->nationality,
                'country_id' => $request->country_id,
                'university_id' => $request->university_id,
                'course_id' => $request->course_id,
                'course_intake_id' => $request->course_intake_id,
                'preferred_intake' => $preferredIntake,
                'cgpa' => $request->cgpa,
                'ielts_score' => $request->ielts_score,
            ]);

            $newDocs = $this->storeUploadedDocuments($request->file('documents'));
            if ($newDocs !== null) {
                $profile->documents = $newDocs;
            }

            $newTrans = $this->storeUploadedDocuments($request->file('translation_docs'));
            if ($newTrans !== null) {
                $profile->translation_documents = $newTrans;
            }

            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Student profile saved successfully!',
                'application_id' => $profile->application_id,
                'data' => $profile->fresh(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //  public function show($id): JsonResponse
    // {
    //     try {
    //         $user = User::where('id', $id)->where('role', 'student')->firstOrFail();
    //         $profile = StudentProfile::where('user_id', $id)->first();

    //         // Combine data so the Vue form can easily map it
    //         $data = array_merge($user->toArray(), $profile ? $profile->toArray() : []);

    //         // Splitting the full_name back into first and last for the form
    //         if ($user->full_name) {
    //             $parts = explode(' ', $user->full_name, 2);
    //             $data['first_name'] = $parts[0];
    //             $data['last_name'] = $parts[1] ?? '';
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => $data,
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Student not found: ' . $e->getMessage(),
    //         ], Response::HTTP_NOT_FOUND);
    //     }
    // }

    /**
     * Update student and their profile.
     */
    public function show($id): JsonResponse
{
    try {
        $user = User::where('id', $id)->where('role', 'student')->firstOrFail();
        $profile = StudentProfile::where('user_id', $id)->first();

        // Combine User and Profile data
        $data = array_merge($user->toArray(), $profile ? $profile->toArray() : []);

        // 1. Fix Names
        if ($user->full_name) {
            $parts = explode(' ', $user->full_name, 2);
            $data['first_name'] = $parts[0];
            $data['last_name'] = $parts[1] ?? '';
        }

        // 2. Fix Date Formats (Essential for HTML <input type="date">)
        $dateFields = ['date_of_birth', 'passport_validity'];
        foreach ($dateFields as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Carbon::parse($data[$field])->format('Y-m-d');
            }
        }

        // 3. Fix Document URLs (Convert paths to full URLs)
        $formatDocs = function($docs) {
            if (!$docs) return [];
            $docsArray = is_array($docs) ? $docs : json_decode($docs, true);
            if (!is_array($docsArray)) return [];

            return array_map(function($path) {
                return [
                    'url' => Storage::disk('public')->url($path),
                    'file_name' => basename($path),
                    'path' => $path
                ];
            }, $docsArray);
        };

        $data['documents'] = $formatDocs($data['documents'] ?? []);
        $data['translation_documents'] = $formatDocs($data['translation_documents'] ?? []);

        return response()->json([
            'success' => true,
            'data' => $data,
        ], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Student not found: ' . $e->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }
}
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::where('id', $id)->where('role', 'student')->firstOrFail();

        $rules = [
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'password' => 'nullable|string|min:6',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'sponsor_phone' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:50',
            'passport_validity' => 'nullable|date',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'course_intake_id' => 'nullable|exists:course_intakes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'translation_docs' => 'nullable|array',
            'translation_docs.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            // 1. Update User Table
            $user->update([
                'full_name' => trim($request->first_name . ' ' . $request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // 2. Update Student Profile Table
            $profile = StudentProfile::firstOrNew(['user_id' => $user->id]);

            $preferredIntake = null;
            if ($request->filled('course_intake_id')) {
                $preferredIntake = CourseIntake::whereKey($request->course_intake_id)->value('intake_name');
            }

            $profile->fill([
                'phone' => $request->phone,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'sponsor_phone' => $request->sponsor_phone,
                'passport_number' => $request->passport_number,
                'passport_validity' => $request->passport_valids,
                'country_id' => $request->country_id,
                'university_id' => $request->university_id,
                'course_id' => $request->course_id,
                'course_intake_id' => $request->course_intake_id,
                'preferred_intake' => $preferredIntake,
            ]);

            // Handle Document Uploads (Append to existing)
            if ($request->hasFile('documents')) {
                $newDocs = $this->storeUploadedDocuments($request->file('documents'));
                $currentDocs = $profile->documents ?? [];
                $profile->documents = array_merge($currentDocs, $newDocs);
            }

            if ($request->hasFile('translation_docs')) {
                $newTrans = $this->storeUploadedDocuments($request->file('translation_docs'));
                $currentTrans = $profile->translation_documents ?? [];
                $profile->translation_documents = array_merge($currentTrans, $newTrans);
            }

            $profile->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully!',
                'data' => $user->load('profile'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>|\Illuminate\Http\UploadedFile|null  $files
     * @return array<int, string>|null
     */
    protected function storeUploadedDocuments($files): ?array
    {
        if ($files === null) {
            return null;
        }

        $list = is_array($files) ? $files : [$files];
        $paths = [];

        foreach ($list as $file) {
            if (! $file) {
                continue;
            }
            $name = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
            $paths[] = $file->storeAs('students/profiles', $name, 'public');
        }

        return $paths === [] ? null : $paths;
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only student accounts can be deleted from here.',
                ], Response::HTTP_FORBIDDEN);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student account deleted successfully.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
