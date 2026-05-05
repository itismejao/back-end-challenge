<?php

namespace Integration\Jobs;

use Integration\Contracts\MusicProviderFactoryInterface;
use Integration\Services\TrackIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchTrackByIsrcJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param list<string> $markets
     */
    public function __construct(
        public readonly string $isrc,
        public readonly string $providerCode = 'spotify',
        public readonly array $markets = [],
    ) {
        $this->onQueue('integration');
    }

    public function handle(
        TrackIngestionService $ingestionService,
        MusicProviderFactoryInterface $providerFactory,
    ): void {
        $provider = $providerFactory->make($this->providerCode);

        Log::info("Fetching track for ISRC [{$this->isrc}] from [{$this->providerCode}]");

        $track = $ingestionService->ingest($this->isrc, $provider, $this->markets);

        if ($track) {
            Log::info("Track [{$track->name}] ingested successfully for ISRC [{$this->isrc}]");
        }
    }
}
