<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_method' => $this->attendance_method->value,
            'minimum_action_interval_minutes' => $this->minimum_action_interval_minutes,
            'grace_minutes' => $this->grace_minutes,
        ];
    }
}
