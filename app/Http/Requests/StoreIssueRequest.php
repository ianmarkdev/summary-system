<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Issue;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'priority'    => ['required', 'in:' . implode(',', Issue::PRIORITIES)],
            'category'    => ['required', 'in:' . implode(',', Issue::CATEGORIES)],
            'status'      => ['sometimes', 'in:' . implode(',', Issue::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.min'          => 'Title must be at least 5 characters.',
            'description.min'    => 'Description must be at least 10 characters.',
            'priority.in'        => 'Priority must be one of: ' . implode(', ', Issue::PRIORITIES) . '.',
            'category.in'        => 'Category must be one of: ' . implode(', ', Issue::CATEGORIES) . '.',
            'status.in'          => 'Status must be one of: ' . implode(', ', Issue::STATUSES) . '.',
        ];
    }
}
