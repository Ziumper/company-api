<?php

namespace App\Factory;

use App\Entity\Employee;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class EmployeeFactory extends PersistentObjectFactory
{
    #[Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => static::faker()->email(),
            'name' => static::faker()->firstName(),
            'surname' => static::faker()->lastName(),
        ];
    }

    public function generateRandomFeed(): array
    {
        return $this->defaults();
    }

    #[Override]
    public static function class(): string
    {
        return Employee::class;
    }
}
