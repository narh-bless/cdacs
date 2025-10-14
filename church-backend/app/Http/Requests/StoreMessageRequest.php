<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can send messages
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:personal,group,broadcast',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'recipient_id' => 'required_if:type,personal|exists:users,id',
            'parent_message_id' => 'nullable|exists:messages,id',
        ];
    }
}
