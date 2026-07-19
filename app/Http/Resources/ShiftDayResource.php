<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftDayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'day_of_week' => $this->day_of_week->value,
            'work_start_time' => $this->work_start_time,
            'work_end_time' => $this->work_end_time,
            'ends_next_day' => $this->ends_next_day,
            'break_duration_minutes' => $this->break_duration_minutes,
        ];
    }
}
