<?php

namespace App\Http\Requests\Employee;

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->employee()->user_id)],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('employees', 'phone')->ignore($this->employee()->id)],
            'address' => ['sometimes', 'string'],
            'hire_date' => ['sometimes', 'date'],
            'identity_document' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:ok2048'],
            'status' => ['sometimes', new Enum(EmployeeStatus::class)],
            'gender' => ['sometimes', new Enum(Gender::class)],
            'department_id' => ['sometimes', 'nullable', Rule::exists('departments', 'id')],
            'shift_id' => ['sometimes', Rule::exists('shifts', 'id')],
        ];
    }

    // Returns the employee from the current route. Used to simplify unique validation rules.
    protected function employee(): Employee
    {
        return $this->route('employee');
    }
}
