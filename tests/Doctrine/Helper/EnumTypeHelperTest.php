<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Helper;

use App\Doctrine\Helper\EnumTypeHelper;
use App\Enum\TaskPriorityEnum;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class EnumTypeHelperTest extends TestCase
{
    public function testGetCreateEnumTypeSQL(): void
    {
        $sql = EnumTypeHelper::getCreateEnumTypeSQL('task_priority_enum', TaskPriorityEnum::class);

        $this->assertStringContainsString('CREATE TYPE task_priority_enum AS ENUM', $sql);
        $this->assertStringContainsString("'basse'", $sql);
        $this->assertStringContainsString("'normale'", $sql);
        $this->assertStringContainsString("'haute'", $sql);
        $this->assertStringContainsString("'critique'", $sql);
    }

    public function testGetCreateEnumTypeSQLWithNonExistentClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        EnumTypeHelper::getCreateEnumTypeSQL('test_enum', 'NonExistentClass');
    }

    public function testGetDropEnumTypeSQL(): void
    {
        $sql = EnumTypeHelper::getDropEnumTypeSQL('task_priority_enum');
        $this->assertEquals('DROP TYPE IF EXISTS task_priority_enum', $sql);
    }

    public function testGetEnumTypeDeclarationSQLWithPostgreSQL(): void
    {
        $platform = $this->createMock(PostgreSQLPlatform::class);
        $sql = EnumTypeHelper::getEnumTypeDeclarationSQL('task_priority_enum', $platform);
        $this->assertEquals('task_priority_enum', $sql);
    }

    public function testGetEnumTypeDeclarationSQLWithOtherPlatform(): void
    {
        // Utilisons un mock générique de AbstractPlatform au lieu de SqlitePlatform
        $platform = $this->createMock(AbstractPlatform::class);
        $platform->expects($this->once())
            ->method('getStringTypeDeclarationSQL')
            ->willReturn('VARCHAR(255)');

        $sql = EnumTypeHelper::getEnumTypeDeclarationSQL('task_priority_enum', $platform);
        $this->assertEquals('VARCHAR(255)', $sql);
    }
}
