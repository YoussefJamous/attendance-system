<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_name' => ['sometimes', 'string', 'max:255'],
            'attendance_date' => ['sometimes', 'date_format:Y-m-d'],
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'status' => ['sometimes', Rule::enum(AttendanceStatus::class)],
            'sort_by' => ['sometimes', Rule::in(['attendance_date', 'status', 'created_at', 'employee_name'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($this->user()->can(Permission::ATTENDANCE_MANAGE->value)) {
                return;
            }

            if ($this->filled('employee_name')) {
                $validator->errors()->add('employee_name', 'This filter is only available to HR users.');
            }
        }];
    }
}
