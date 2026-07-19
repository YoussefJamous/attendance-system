<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'department' => new DepartmentResource(($this->whenLoaded('department'))),
            'shift' => new ShiftResource($this->whenLoaded('shift')),
            'name' => $this->fullName(),
            'phone' => $this->phone,
            'address' => $this->address,
            'hire_date' => $this->hire_date?->toDateString(),
            'identity_document_path' => $this->identity_document_path,
            'status' => $this->status->value,
            'gender' => $this->gender->value,
        ];
    }
}
