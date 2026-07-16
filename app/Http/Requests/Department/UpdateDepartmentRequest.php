<?php

namespace App\Http\Requests\Department;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('departments', 'code')->ignore($this->department()->id),],
            'description' => ['nullable', 'string'],
        ];
    }

    // Returns the department from the current route. Used to simplify unique validation rules.
    protected function department(): Department
    {
        return $this->route('department');
    }
}
