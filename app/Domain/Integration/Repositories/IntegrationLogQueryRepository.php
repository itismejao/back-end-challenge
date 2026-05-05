<?php

namespace Integration\Repositories;

use Integration\Contracts\IntegrationLogQueryInterface;
use Integration\Models\IntegrationLog;
use Illuminate\Contracts\Pagination\CursorPaginator;

class IntegrationLogQueryRepository implements IntegrationLogQueryInterface
{
    private const DEFAULT_PER_PAGE = 20;

    public function paginate(array $filters = []): CursorPaginator
    {
        $query = IntegrationLog::query()->with('track:id,name,isrc');

        if (! empty($filters['provider_code'])) {
            $query->where('provider_code', $filters['provider_code']);
        }

        if (! empty($filters['isrc'])) {
            $query->where('isrc', $filters['isrc']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->where('started_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('started_at', '<=', $filters['to']);
        }

        $perPage = $filters['per_page'] ?? self::DEFAULT_PER_PAGE;

        return $query
            ->orderByDesc('id')
            ->cursorPaginate($perPage);
    }
}
