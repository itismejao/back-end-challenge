<?php

namespace Tests\Unit\Enums;

use App\Domain\Music\Enums\AvailabilityMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AvailabilityModeTest extends TestCase
{
    #[Test]
    public function it_has_expected_cases(): void
    {
        $this->assertSame('global', AvailabilityMode::Global->value);
        $this->assertSame('markets', AvailabilityMode::Markets->value);
        $this->assertSame('unknown', AvailabilityMode::Unknown->value);
    }
}
