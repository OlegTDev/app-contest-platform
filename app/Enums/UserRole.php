<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case MODERATOR = 'moderator';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'Пользователь',
            self::MODERATOR => 'Модератор',
            self::ADMIN => 'Администратор',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function selectOptions(): array
    {
        return array_map(fn (self $enum) => [
            'label' => $enum->label(),
            'value' => $enum->value,
        ], self::cases());
    }
}
