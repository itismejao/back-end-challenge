<?php

namespace App\Domain\Integration\Console;

use App\Domain\Integration\Jobs\FetchTrackByIsrcJob;
use Illuminate\Console\Command;

class FetchTracksByIsrcCommand extends Command
{
    protected $signature = 'tracks:fetch
        {isrcs* : One or more ISRC codes to fetch}
        {--provider=spotify : The music provider to use}
        {--markets= : Comma-separated market codes to check availability (e.g. BR,US,GB)}';

    protected $description = 'Dispatch jobs to fetch track data by ISRC codes';

    public function handle(): int
    {
        $isrcs = $this->argument('isrcs');
        $provider = $this->option('provider');
        $markets = $this->parseMarkets($this->option('markets'));

        foreach ($isrcs as $isrc) {
            FetchTrackByIsrcJob::dispatch($isrc, $provider, $markets);
            $this->info("Dispatched job for ISRC [{$isrc}] on [{$provider}]");
        }

        $this->info(count($isrcs).' job(s) dispatched.');

        if (! empty($markets)) {
            $this->info('Markets: '.implode(', ', $markets));
        }

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function parseMarkets(?string $markets): array
    {
        if (! $markets) {
            return [];
        }

        return array_map('strtoupper', array_map('trim', explode(',', $markets)));
    }
}
