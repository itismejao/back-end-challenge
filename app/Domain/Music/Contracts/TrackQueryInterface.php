<?php

namespace Music\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;

interface TrackQueryInterface
{
    /**
     * @param array{
     *     order_by?: string,
     *     direction?: string,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): CursorPaginator;
}
