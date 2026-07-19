<?php

namespace App\Models;

use App\Enums\AttendanceAction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_id',
        'action',
        'occurred_at',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'action' => AttendanceAction::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
