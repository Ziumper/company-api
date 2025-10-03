<?php

namespace App\Factory;

use App\Entity\Company;
use Override;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;


final class CompanyFactory extends PersistentObjectFactory
{
   
    #[Override]
    protected function defaults(): array|callable {
        return [
            'name' => static::faker()->company(),
            'street' => static::faker()->streetAddress(),
            'taxReferenceNumber' => static::faker()->numerify("##########"),
            'town' => static::faker()->city(),
            'zipcode' => static::faker()->postcode()
        ];
    }
    
    public function generateRandomFeed(): array {
        return $this->defaults();
    }

    #[Override]
    public static function class(): string {
        return Company::class;
    }
}
