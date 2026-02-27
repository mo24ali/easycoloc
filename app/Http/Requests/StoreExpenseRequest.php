<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->collocation);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        $fail('Please select a category.');
                    } elseif ($value !== 'new') {
                        if (!is_numeric($value) || !\App\Models\Category::where('id', (int) $value)->exists()) {
                            $fail('The selected category is invalid.');
                        }
                    }
                },
            ],
            'new_category' => ['required_if:category_id,new', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'expense_date' => ['required', 'date'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'The expense amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
            'title.required' => 'The expense title is required.',
            'title.max' => 'The title must not exceed 255 characters.',
            'category_id.required' => 'A category is required.',
            'new_category.required_if' => 'The new category name is required when creating a new category.',
            'description.max' => 'The description must not exceed 500 characters.',
            'expense_date.required' => 'The expense date is required.',
            'expense_date.date' => 'The expense date must be a valid date.',
        ];
    }
}
