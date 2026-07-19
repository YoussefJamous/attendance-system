<?php

namespace App\Models;

use App\Enums\AttendanceMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_method',
        'minimum_action_interval_minutes',
        'grace_minutes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_method' => AttendanceMethod::class,
        ];
    }
}
