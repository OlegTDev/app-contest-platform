<?php

declare(strict_types=1);

namespace App\Enums;

enum ContestType: string
{
    case QUIZ = 'quiz';
    case VOTING = 'voting';

    public function label(): string
    {
        return match($this) {
            self::QUIZ => 'Викторина',
            self::VOTING => 'Голосование',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function selectOptions(): array
    {
        return array_map(fn(self $enum) => [
            'label' => $enum->label(),
            'value' => $enum->value,
        ], self::cases());
    }
}
