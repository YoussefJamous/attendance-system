<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceCorrectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'employee_id' => $this->attendance->employee_id,
            'employee_name' => $this->attendance->employee->fullName(),
            'attendance_date' => $this->attendance->attendance_date->toDateString(),
            'note' => $this->note,
            'status' => $this->status->value,
            'logs' => AttendanceCorrectionLogResource::collection($this->whenLoaded('logs')),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
