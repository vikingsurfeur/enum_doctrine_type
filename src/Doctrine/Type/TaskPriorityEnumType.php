<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\Enum\TaskPriorityEnum;

class TaskPriorityEnumType extends AbstractEnumType
{
    public static function getTypeName(): string
    {
        return 'task_priority_enum';
    }

    protected static function getEnumClass(): string
    {
        return TaskPriorityEnum::class;
    }
}
