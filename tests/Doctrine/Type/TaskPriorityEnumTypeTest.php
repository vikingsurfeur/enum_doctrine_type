<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Type;

use App\Doctrine\Type\TaskPriorityEnumType;
use App\Enum\TaskPriorityEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class TaskPriorityEnumTypeTest extends TestCase
{
    private TaskPriorityEnumType $type;
    private AbstractPlatform $platform;

    protected function setUp(): void
    {
        $this->type = new TaskPriorityEnumType();
        $this->platform = $this->createMock(AbstractPlatform::class);
    }

    public function testGetName(): void
    {
        $this->assertEquals('task_priority_enum', $this->type->getName());
    }

    public function testGetSQLDeclaration(): void
    {
        $this->assertEquals(
            'task_priority_enum',
            $this->type->getSQLDeclaration([], $this->platform)
        );
    }

    public function testRequiresSQLCommentHint(): void
    {
        $this->assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }

    public function testConvertToDatabaseValue(): void
    {
        $this->assertEquals(
            'basse',
            $this->type->convertToDatabaseValue(TaskPriorityEnum::LOW, $this->platform)
        );

        $this->assertEquals(
            'normale',
            $this->type->convertToDatabaseValue(TaskPriorityEnum::MEDIUM, $this->platform)
        );

        $this->assertEquals(
            'haute',
            $this->type->convertToDatabaseValue(TaskPriorityEnum::HIGH, $this->platform)
        );

        $this->assertEquals(
            'critique',
            $this->type->convertToDatabaseValue(TaskPriorityEnum::CRITICAL, $this->platform)
        );

        $this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
    }

    public function testConvertToPHPValue(): void
    {
        $this->assertEquals(
            TaskPriorityEnum::LOW,
            $this->type->convertToPHPValue('basse', $this->platform)
        );

        $this->assertEquals(
            TaskPriorityEnum::MEDIUM,
            $this->type->convertToPHPValue('normale', $this->platform)
        );

        $this->assertEquals(
            TaskPriorityEnum::HIGH,
            $this->type->convertToPHPValue('haute', $this->platform)
        );

        $this->assertEquals(
            TaskPriorityEnum::CRITICAL,
            $this->type->convertToPHPValue('critique', $this->platform)
        );

        $this->assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testConvertToPHPValueWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->type->convertToPHPValue('valeur_invalide', $this->platform);
    }

    public function testGetCreateTypeSQL(): void
    {
        $sql = TaskPriorityEnumType::getCreateTypeSQL();
        $this->assertStringContainsString('CREATE TYPE task_priority_enum AS ENUM', $sql);
    }

    public function testGetDropTypeSQL(): void
    {
        $sql = TaskPriorityEnumType::getDropTypeSQL();
        $this->assertEquals('DROP TYPE IF EXISTS task_priority_enum', $sql);
    }
}
