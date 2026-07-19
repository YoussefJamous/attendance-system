<?php

namespace App\Http\Requests\Shift;

use App\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'days' => ['required', 'array', 'min:1'],
            'days.*.day_of_week' => ['required', 'distinct', new Enum(DayOfWeek::class)],
            'days.*.work_start_time' => ['required', 'date_format:H:i'],
            'days.*.work_end_time' => ['required', 'date_format:H:i'],
            'days.*.break_duration_minutes' => ['required', 'integer', 'min:0'],
        ];
    }
}
