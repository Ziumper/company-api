<?php

namespace App\Factory;

use App\Entity\Company;
use DateTime;
use DateTimeImmutable;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;


final class CompanyFactory extends PersistentObjectFactory
{
   
    #[Override]
    protected function defaults(): array|callable {
        return [
            'createdAt' => DateTimeImmutable::createFromMutable(new DateTime("now")),
            'name' => static::faker()->company(),
            'street' => static::faker()->streetAddress(),
            'taxReferenceNumber' => static::faker()->numerify("##########"),
            'town' => static::faker()->city(),
            'updatedAt' => DateTimeImmutable::createFromMutable(new DateTime("now")),
            'zipcode' => static::faker()->postcode()
        ];
    }

    #[Override]
    public static function class(): string {
        return Company::class;
    }
}
