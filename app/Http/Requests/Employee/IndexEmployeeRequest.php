<?php

namespace App\Http\Requests\Employee;


use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class IndexEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'status' => ['nullable', new Enum(EmployeeStatus::class)],
            'gender' => ['nullable', new Enum(Gender::class)],
            'sort_by' => ['nullable', 'string', Rule::in(['name', 'hire_date', 'status', 'gender'])],
            'search' => ['nullable', 'string'],
        ];
    }
}
