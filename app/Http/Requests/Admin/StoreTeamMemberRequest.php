<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg,avif|max:2048',
            'status' => 'nullable|in:1,0,true,false,"1","0","true","false"',
            'content' => 'nullable|string',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|string|max:255',
            'social_links.twitter' => 'nullable|string|max:255',
            'social_links.linkedin' => 'nullable|string|max:255',
            'social_links.instagram' => 'nullable|string|max:255',
        ];
    }
}
