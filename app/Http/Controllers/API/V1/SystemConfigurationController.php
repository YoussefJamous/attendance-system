<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\SystemConfiguration\UpdateSystemConfigurationRequest;
use App\Http\Resources\SystemConfigurationResource;
use App\Models\SystemConfiguration;
use App\Services\SystemConfigurationService;
use Illuminate\Http\JsonResponse;

class SystemConfigurationController extends ApiController
{
    public function __construct(private readonly SystemConfigurationService $systemConfigurationService) {}

    public function show(): JsonResponse
    {
        $this->authorize('viewAny', SystemConfiguration::class);

        $configuration = $this->systemConfigurationService->current();

        return $this->success(
            $configuration ? new SystemConfigurationResource($configuration) : null,
            $configuration ? 'System configuration retrieved successfully.' : 'System configuration has not been completed.'
        );
    }

    public function update(UpdateSystemConfigurationRequest $request): JsonResponse
    {
        $this->authorize('manage', SystemConfiguration::class);

        return $this->success(
            new SystemConfigurationResource($this->systemConfigurationService->save($request->validated())),
            'System configuration saved successfully.'
        );
    }
}
