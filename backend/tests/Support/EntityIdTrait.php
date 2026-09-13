<?php

namespace App\Tests\Support;

/**
 * Les entités n'exposent pas de setter d'identifiant (généré par la base) :
 * les tests unitaires le posent par réflexion.
 */
trait EntityIdTrait
{
    /**
     * @template T of object
     * @param T $entity
     * @return T
     */
    private function withId(object $entity, int $id): object
    {
        (new \ReflectionProperty($entity, 'id'))->setValue($entity, $id);

        return $entity;
    }
}
