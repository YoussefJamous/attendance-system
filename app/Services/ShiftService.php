<?php

namespace App\Services;

use App\Models\Shift;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftService
{
    public function index(array $filters, int $perPage): LengthAwarePaginator
    {
        return Shift::query()
            ->with('days')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function show(Shift $shift): Shift
    {
        return $shift->load('days');
    }

    public function store(array $data): Shift
    {
        return DB::transaction(function () use ($data) {
            $shift = Shift::create(Arr::except($data, 'days'));
            $shift->days()->createMany($data['days']);

            return $shift->load('days');
        });
    }

    public function update(Shift $shift, array $data): Shift
    {
        return DB::transaction(function () use ($shift, $data) {
            $shift->update(Arr::except($data, 'days'));
            $shift->days()->delete();
            $shift->days()->createMany($data['days']);

            return $shift->refresh()->load('days');
        });
    }

    public function delete(Shift $shift): void
    {
        if ($shift->employees()->exists()) {
            throw ValidationException::withMessages([
                'shift' => ['A shift assigned to employees cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($shift) {
            $shift->delete();
        });
    }
}
