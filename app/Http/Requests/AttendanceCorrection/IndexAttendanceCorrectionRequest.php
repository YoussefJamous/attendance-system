<?php

namespace App\Http\Requests\AttendanceCorrection;

use App\Enums\AttendanceCorrectionStatus;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(AttendanceCorrectionStatus::class)],
            'attendance_date' => ['sometimes', 'date_format:Y-m-d'],
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'employee_id' => ['sometimes', 'uuid', 'exists:employees,id'],
            'employee_name' => ['sometimes', 'string', 'max:255'],
            'department_id' => ['sometimes', 'uuid', 'exists:departments,id'],
            'sort_by' => ['sometimes', Rule::in(['created_at', 'attendance_date', 'status'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($this->user()->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value)) {
                return;
            }

            foreach (['employee_id', 'employee_name', 'department_id'] as $field) {
                if ($this->filled($field)) {
                    $validator->errors()->add($field, 'This filter is only available to HR users.');
                }
            }
        }];
    }
}
