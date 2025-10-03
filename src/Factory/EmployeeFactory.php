<?php

namespace App\Factory;

use App\Entity\Employee;
use DateTime;
use DateTimeImmutable;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class EmployeeFactory extends PersistentObjectFactory
{

    #[Override]
    protected function defaults(): array|callable {
        return [
            'createdAt' => DateTimeImmutable::createFromMutable(new DateTime("now")),
            'email' => static::faker()->email(),
            'name' => static::faker()->firstName(),
            'surname' => static::faker()->lastName(),
            'updatedAt' => DateTimeImmutable::createFromMutable(new DateTime('now'))
        ];
    }

    #[Override]
    public static function class(): string {
        return Employee::class;
    }
}
