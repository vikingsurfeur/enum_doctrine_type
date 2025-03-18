<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Enum\TaskPriorityEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TaskPriorityEnumType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getEnumDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof TaskPriorityEnum ? $value->value : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TaskPriorityEnum
    {
        if ($value === null) {
            return null;
        }

        return TaskPriorityEnum::from($value);
    }

    public function getName(): string
    {
        return 'task_priority_enum';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
