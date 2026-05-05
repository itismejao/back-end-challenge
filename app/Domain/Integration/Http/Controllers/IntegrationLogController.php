<?php

namespace Integration\Http\Controllers;

use Integration\Contracts\IntegrationLogQueryInterface;
use Integration\Http\Requests\IntegrationLogIndexRequest;
use Integration\Http\Resources\IntegrationLogResource;

class IntegrationLogController
{
    public function index(IntegrationLogIndexRequest $request, IntegrationLogQueryInterface $logQuery)
    {
        $logs = $logQuery->paginate($request->validated());

        return IntegrationLogResource::collection($logs);
    }
}
