<?php

namespace App\Http\Requests\Holiday;

use App\Models\Holiday;

class UpdateHolidayRequest extends StoreHolidayRequest
{
    protected function prepareForValidation(): void
    {
        $holiday = $this->holiday();

        $this->merge([
            'start_date' => $this->input('start_date', $holiday->start_date->toDateString()),
            'end_date' => $this->input('end_date', $holiday->end_date->toDateString()),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ];
    }

    private function holiday(): Holiday
    {
        return $this->route('holiday');
    }
}
