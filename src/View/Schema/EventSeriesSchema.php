<?php
namespace App\View\Schema;

use App\Model\Entity\EventSeries;
use JsonApi\View\Schema\EntitySchema;

class EventSeriesSchema extends EntitySchema
{
    /**
     * Returns the event series's ID
     *
     * @param \Cake\ORM\Entity $entity EventSeries entity
     * @return string
     */
    public function getId($entity): string
    {
        return (string)$entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($entity, array $fieldKeysFilter = null): array
    {
        return [
            'title' => $entity->title
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param EventSeries $entity Entity
     * @param bool $isPrimary Is primary flag
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($entity, bool $isPrimary, array $includeRelationships): ?array
    {
        return [
            'events' => [
                self::DATA => $entity->events
            ],
            'user' => [
                self::DATA => $entity->user
            ]
        ];
    }
}
