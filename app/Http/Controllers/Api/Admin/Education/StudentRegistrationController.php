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
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentRegisteredMail;

class StudentRegistrationController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $students = User::where('role', 'student')
                ->with(['profile', 'consultant:id,full_name,email'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($student) {
                    return array_merge($student->toArray(), [
                        'created_by' => $student->consultant
                            ? $student->consultant->full_name
                            : 'Admin',
                    ]);
                });

            return response()->json([
                'success' => true,
                'data' => $students,
                'message' => 'Students retrieved successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: '.$e->getMessage(),
            ], 500);
        }
    }

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
                'last_education_level' => 'nullable|string|max:50',
                'cgpa' => 'nullable|numeric|min:0|max:5',
                'ielts_score' => 'nullable|numeric|min:0|max:9',
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

            try {
                Mail::to($user->email)->send(new StudentRegisteredMail(
                    $user->full_name,
                    $user->email,
                    $request->password,
                    auth()->user()->full_name ?? null
                ));
            } catch (\Exception $mailException) {
                \Illuminate\Support\Facades\Log::error('Failed to send registration email: ' . $mailException->getMessage());
            }

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

                $preferredIntake = $request->filled('course_intake_id')
                    ? CourseIntake::whereKey($request->course_intake_id)->value('intake_name')
                    : null;

                $profile = StudentProfile::create([
                    'user_id' => $user->id,
                    'phone' => $request->phone,
                    'father_name' => $request->father_name,
                    'mother_name' => $request->mother_name,
                    'date_of_birth' => $request->date_of_birth,
                    'address' => $request->address,
                    'sponsor_phone' => $request->sponsor_phone,
                    'passport_number' => $request->passport_number,
                    'passport_validity' => $request->passport_validity,
                    'last_education_level' => $request->last_education_level,
                    'cgpa' => $request->cgpa,
                    'ielts_score' => $request->ielts_score,
                    'country_id' => $request->country_id,
                    'university_id' => $request->university_id,
                    'course_id' => $request->course_id,
                    'course_intake_id' => $request->course_intake_id,
                    'preferred_intake' => $preferredIntake,
                    'documents' => $this->storeUploadedDocuments($request->file('documents')),
                    'translation_documents' => $this->storeUploadedDocuments($request->file('translation_docs')),
                ]);

                return compact('user', 'profile');
            });

            try {
                Mail::to($result['user']->email)->send(new StudentRegisteredMail(
                    $result['user']->full_name,
                    $result['user']->email,
                    $request->password,
                    auth()->user()->full_name ?? null
                ));
            } catch (\Exception $mailException) {
                \Illuminate\Support\Facades\Log::error('Failed to send registration email (with profile): ' . $mailException->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Student account and profile created successfully.',
                'data' => [
                    'user' => $result['user'],
                    'profile' => $result['profile']
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
            'address' => 'nullable|string',
            'sponsor_phone' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:50',
            'passport_validity' => 'nullable|date',
            'university_id' => 'nullable|exists:universities,id',
            'course_id' => 'nullable|exists:courses,id',
            'course_intake_id' => 'nullable|exists:course_intakes,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'translation_docs' => 'nullable|array',
            'translation_docs.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $profile = StudentProfile::firstOrNew(['user_id' => $request->user_id]);

            $preferredIntake = $request->filled('course_intake_id')
                ? CourseIntake::whereKey($request->course_intake_id)->value('intake_name')
                : $request->preferred_intake;

            $profile->fill([
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
            ]);

            if ($request->hasFile('documents')) {
                $profile->documents = $this->storeUploadedDocuments($request->file('documents'));
            }
            if ($request->hasFile('translation_docs')) {
                $profile->translation_documents = $this->storeUploadedDocuments($request->file('translation_docs'));
            }

            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Student profile saved successfully!',
                'data' => $profile->fresh()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $user = User::where('id', $id)->where('role', 'student')->firstOrFail();
            $profile = StudentProfile::where('user_id', $id)->first();

            $data = array_merge($user->toArray(), $profile ? $profile->toArray() : []);

            if ($user->full_name) {
                $parts = explode(' ', $user->full_name, 2);
                $data['first_name'] = $parts[0];
                $data['last_name'] = $parts[1] ?? '';
            }

            $dateFields = ['date_of_birth', 'passport_validity'];
            foreach ($dateFields as $field) {
                if (!empty($data[$field])) {
                    $data[$field] = Carbon::parse($data[$field])->format('Y-m-d');
                }
            }

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

            return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Student not found'], Response::HTTP_NOT_FOUND);
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

            $user->update([
                'full_name' => trim($request->first_name . ' ' . $request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            $profile = StudentProfile::firstOrNew(['user_id' => $user->id]);

            // Multipart / optional fields: if a key is absent from the body, $request->input() is null
            // and would wipe FKs. Only overwrite nullable FKs when the client actually sent the key.
            $input = $request->input();
            $nullableFk = ['university_id', 'course_id', 'course_intake_id'];
            $fkValues = [];
            foreach ($nullableFk as $key) {
                if (array_key_exists($key, $input)) {
                    $raw = $input[$key];
                    $fkValues[$key] = ($raw === '' || $raw === null) ? null : $raw;
                } elseif ($profile->exists) {
                    $fkValues[$key] = $profile->getAttribute($key);
                } else {
                    $fkValues[$key] = null;
                }
            }

            $preferredIntake = ! empty($fkValues['course_intake_id'])
                ? CourseIntake::whereKey($fkValues['course_intake_id'])->value('intake_name')
                : null;

            $profile->fill([
                'phone' => $request->phone,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
                'sponsor_phone' => $request->sponsor_phone,
                'passport_number' => $request->passport_number,
                'passport_validity' => $request->passport_validity,
                'country_id' => $request->country_id,
                'university_id' => $fkValues['university_id'],
                'course_id' => $fkValues['course_id'],
                'course_intake_id' => $fkValues['course_intake_id'],
                'preferred_intake' => $preferredIntake,
            ]);

            if ($request->hasFile('documents')) {
                $newDocs = $this->storeUploadedDocuments($request->file('documents'));
                $profile->documents = array_merge($profile->documents ?? [], $newDocs);
            }
            if ($request->hasFile('translation_docs')) {
                $newTrans = $this->storeUploadedDocuments($request->file('translation_docs'));
                $profile->translation_documents = array_merge($profile->translation_documents ?? [], $newTrans);
            }

            $profile->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Student updated successfully!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeDocument(Request $request, $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:documents,translation_documents',
            'path' => 'required|string',
        ]);

        try {
            $profile = StudentProfile::where('user_id', $id)->firstOrFail();
            $column = $request->type;
            $files = $profile->$column ?? [];

            if (!is_array($files)) $files = json_decode($files, true) ?: [];

            $files = array_filter($files, fn($path) => $path !== $request->path);

            Storage::disk('public')->delete($request->path);
            $profile->$column = array_values($files);
            $profile->save();

            return response()->json(['success' => true, 'message' => 'Document removed.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function storeUploadedDocuments($files): ?array
    {
        if (!$files) return null;
        $list = is_array($files) ? $files : [$files];
        $paths = [];
        foreach ($list as $file) {
            if (!$file) continue;
            $name = (string) Str::uuid().'.'.$file->getClientOriginalExtension();
            $paths[] = $file->storeAs('students/profiles', $name, 'public');
        }
        return $paths === [] ? null : $paths;
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            if ($user->role !== 'student') return response()->json(['success' => false, 'message' => 'Invalid role'], Response::HTTP_FORBIDDEN);
            $user->delete();
            return response()->json(['success' => true, 'message' => 'Student deleted.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
