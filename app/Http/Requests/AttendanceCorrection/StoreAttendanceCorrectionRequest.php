<?php

namespace App\Http\Requests\AttendanceCorrection;

use App\Enums\AttendanceAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => ['required', 'uuid', 'exists:attendances,id'],
            'note' => ['required', 'string', 'max:1000'],
            'logs' => ['required', 'array', 'min:2'],
            'logs.*.action' => ['required', Rule::enum(AttendanceAction::class)],
            'logs.*.action_at' => ['required', 'date'],
        ];
    }
}
