<?php
namespace App\View\Schema;

use JsonApi\View\Schema\EntitySchema;

class TagSchema extends EntitySchema
{
    /**
     * Returns the tag's ID
     *
     * @param \Cake\ORM\Entity $entity Tag entity
     * @return int
     */
    public function getId($entity)
    {
        return $entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return array
     */
    public function getAttributes($entity)
    {
        return [
            'name' => $entity->name
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($entity, array $includeRelationships = [])
    {
        return [];
    }
}
