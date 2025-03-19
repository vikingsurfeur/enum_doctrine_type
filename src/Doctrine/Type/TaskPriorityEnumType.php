<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Enum\TaskPriorityEnum;

class TaskPriorityEnumType extends AbstractEnumType
{
    public const TYPE_NAME = 'task_priority_enum';

    public static function getTypeName(): string
    {
        return self::TYPE_NAME;
    }

    protected static function getEnumClass(): string
    {
        return TaskPriorityEnum::class;
    }
}
