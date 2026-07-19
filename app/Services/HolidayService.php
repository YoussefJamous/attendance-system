<?php

namespace App\Services;

use App\Imports\HolidayImport;
use App\Models\Holiday;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Exceptions\UnreadableFileException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

class HolidayService
{
    public function index(array $filters, int $perPage): LengthAwarePaginator
    {
        return Holiday::query()
            ->orderBy('start_date')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function store(array $data): Holiday
    {
        return DB::transaction(fn () => Holiday::create($data));
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $holiday->update($data);

            return $holiday->refresh();
        });
    }

    public function delete(Holiday $holiday): void
    {
        DB::transaction(fn () => $holiday->delete());
    }

    public function import(UploadedFile $file): int
    {
        try {
            $rows = Excel::toCollection(new HolidayImport, $file)->first() ?? collect();
        } catch (NoTypeDetectedException|UnreadableFileException|ReaderException) {
            throw ValidationException::withMessages([
                'file' => ['The import file could not be read.'],
            ]);
        }

        $holidays = $this->validatedImportRows($rows);

        if ($holidays->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => ['The import file must contain at least one holiday row.'],
            ]);
        }

        DB::transaction(function () use ($holidays) {
            $holidays->each(fn (array $holiday) => Holiday::create($holiday));
        });

        return $holidays->count();
    }

    public function isHoliday(CarbonInterface|string $date): bool
    {
        $date = Carbon::parse($date)->toDateString();

        return Holiday::query()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    private function validatedImportRows(Collection $rows): Collection
    {
        $errors = [];

        $holidays = $rows
            ->values()
            ->filter(fn (Collection $row) => $row->filter()->isNotEmpty())
            ->map(function (Collection $row, int $index) use (&$errors) {
                $validator = Validator::make($row->toArray(), [
                    'name' => ['required', 'string', 'max:255'],
                    'description' => ['nullable', 'string'],
                    'start_date' => ['required', 'date_format:Y-m-d'],
                    'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $field => $messages) {
                        $errors['rows.'.($index + 2).".{$field}"] = $messages;
                    }

                    return [];
                }

                return $validator->validated();
            });

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $holidays;
    }
}
