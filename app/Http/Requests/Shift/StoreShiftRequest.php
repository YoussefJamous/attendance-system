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
            'days.*.ends_next_day' => ['required', 'boolean'],
            'days.*.break_duration_minutes' => ['required', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [function ($validator) {
            foreach ($this->input('days', []) as $index => $day) {
                if (! isset($day['work_start_time'], $day['work_end_time'], $day['ends_next_day'])) {
                    continue;
                }

                $isOvernight = filter_var($day['ends_next_day'], FILTER_VALIDATE_BOOLEAN);
                $isValid = $isOvernight
                    ? $day['work_end_time'] < $day['work_start_time']
                    : $day['work_end_time'] > $day['work_start_time'];

                if (! $isValid) {
                    $validator->errors()->add(
                        "days.{$index}.work_end_time",
                        $isOvernight
                            ? 'The work end time must be earlier than the work start time for an overnight shift day.'
                            : 'The work end time must be later than the work start time unless the shift day ends the next day.'
                    );
                }
            }
        }];
    }
}
