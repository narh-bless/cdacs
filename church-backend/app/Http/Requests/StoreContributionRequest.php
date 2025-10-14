<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContributionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['administrator', 'finance_committee']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:tithe,offering,donation,special,building_fund,mission,other',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|size:3',
            'payment_method' => 'required|in:cash,check,card,bank_transfer,online,other',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'contribution_date' => 'required|date',
            'status' => 'sometimes|in:pending,confirmed,cancelled',
        ];
    }
}
