<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskPriorityEnum: string
{
    case LOW = 'basse';
    case MEDIUM = 'normale';
    case HIGH = 'haute';
    case CRITICAL = 'critique';
}
