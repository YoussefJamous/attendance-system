<?php

namespace App\Services;

use App\Models\SystemConfiguration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SystemConfigurationService
{
    public function current(): ?SystemConfiguration
    {
        return SystemConfiguration::query()->first();
    }

    public function save(array $data): SystemConfiguration
    {
        return DB::transaction(function () use ($data) {
            $configuration = SystemConfiguration::query()->lockForUpdate()->first();

            if ($configuration) {
                $configuration->update($data);

                return $configuration->refresh();
            }

            return SystemConfiguration::create([
                'attendance_method' => $data['attendance_method'],
                'minimum_action_interval_minutes' => Arr::get($data, 'minimum_action_interval_minutes', 1),
                'grace_minutes' => Arr::get($data, 'grace_minutes', 0),
            ]);
        });
    }
}
