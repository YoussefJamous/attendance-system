<?php

namespace App\Http\Requests\SystemConfiguration;

use App\Enums\AttendanceMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSystemConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_method' => ['required', new Enum(AttendanceMethod::class)],
            'minimum_action_interval_minutes' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'grace_minutes' => ['sometimes', 'integer', 'min:0', 'max:120'],
        ];
    }
}
