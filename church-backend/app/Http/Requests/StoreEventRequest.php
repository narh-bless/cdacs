<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['pastor', 'administrator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'type' => 'required|in:service,meeting,celebration,outreach,ministry,other',
            'status' => 'sometimes|in:draft,published,cancelled,completed',
            'max_attendees' => 'nullable|integer|min:1',
            'requires_registration' => 'boolean',
            'registration_notes' => 'nullable|string|max:500',
        ];
    }
}
