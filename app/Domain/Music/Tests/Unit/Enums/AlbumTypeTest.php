<?php

namespace Music\Tests\Unit\Enums;

use Music\Enums\AlbumType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AlbumTypeTest extends TestCase
{
    #[Test]
    #[DataProvider('validValues')]
    public function it_creates_from_valid_values(string $value, AlbumType $expected): void
    {
        $this->assertSame($expected, AlbumType::from($value));
    }

    public static function validValues(): array
    {
        return [
            ['album', AlbumType::Album],
            ['single', AlbumType::Single],
            ['compilation', AlbumType::Compilation],
            ['ep', AlbumType::Ep],
        ];
    }

    #[Test]
    public function it_throws_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        AlbumType::from('invalid');
    }
}
