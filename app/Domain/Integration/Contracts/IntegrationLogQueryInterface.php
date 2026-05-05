<?php

namespace Integration\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;

interface IntegrationLogQueryInterface
{
    public function paginate(array $filters = []): CursorPaginator;
}
