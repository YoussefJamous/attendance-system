<?php

namespace App\Models;

use App\Enums\AttendanceAction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrectionLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_correction_id',
        'action',
        'action_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => AttendanceAction::class,
            'action_at' => 'datetime',
        ];
    }

    public function correction(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrection::class, 'attendance_correction_id');
    }
}
