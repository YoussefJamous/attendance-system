<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'sort_by' => ['sometimes', 'string', Rule::in(['name', 'employees_count', 'code'])],
            'search' => ['sometimes', 'string'],
        ];
    }
}
