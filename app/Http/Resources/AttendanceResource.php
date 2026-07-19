<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'attendance_date' => $this->attendance_date->toDateString(),
            'status' => $this->status->value,
            'logs' => AttendanceLogResource::collection($this->whenLoaded('logs')),
        ];
    }
}
