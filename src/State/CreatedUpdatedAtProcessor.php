<?php

namespace App\State;

use ApiPlatform\State\ProcessorInterface;

final class CreatedUpdatedAtProcessor implements ProcessorInterface {
    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, $uriVariables = [], $context = []): mixed {
        if($operation instanceof \ApiPlatform\Metadata\Post) 
        {
            $data->setCreatedAt(\DateTimeImmutable::createFromMutable(new \DateTime("now")));
        }

        $data->setUpdatedAt(\DateTimeImmutable::createFromMutable(new \DateTime("now")));
        return $data;
    }

}