<?php

namespace App\Models;

use App\Enums\AttendanceCorrectionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceCorrection extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_id',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttendanceCorrectionStatus::class,
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceCorrectionLog::class)->orderBy('action_at');
    }
}
