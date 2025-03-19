<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Type;

use App\Doctrine\Type\AbstractEnumType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class AbstractEnumTypeTest extends TestCase
{
    public function testConvertToPHPValueWithNonBackedEnum(): void
    {
        $type = new class extends AbstractEnumType {
            protected static function getEnumClass(): string
            {
                return \App\Tests\Doctrine\Type\TestEnum::class;
            }

            public static function getTypeName(): string
            {
                return 'test_enum';
            }
        };

        $platform = $this->createMock(AbstractPlatform::class);

        $this->assertEquals(TestEnum::A, $type->convertToPHPValue('A', $platform));
        $this->assertNull($type->convertToPHPValue(null, $platform));

        $this->expectException(\InvalidArgumentException::class);
        $type->convertToPHPValue('INVALID', $platform);
    }
}
