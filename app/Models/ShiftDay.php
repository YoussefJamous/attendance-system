<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftDay extends Model
{
    use HasUuids;

    protected $fillable = [
        'day_of_week',
        'work_start_time',
        'work_end_time',
        'ends_next_day',
        'break_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'ends_next_day' => 'boolean',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
