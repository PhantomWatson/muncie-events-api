<?php
namespace App\View\Schema;

use JsonApi\View\Schema\EntitySchema;

class EventSeriesSchema extends EntitySchema
{
    /**
     * Returns the event series's ID
     *
     * @param \Cake\ORM\Entity $entity EventSeries entity
     * @return int
     */
    public function getId($entity)
    {
        return $entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param \Cake\ORM\Entity $entity
     * @return array
     */
    public function getAttributes($entity)
    {
        return [
            'title' => $entity->title
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
        return [
            'events' => [
                self::DATA => $entity->events
            ],
            'series' => [
                self::DATA => $entity->event_series
            ],
            'tags' => [
                self::DATA => $entity->tags
            ],
            'user' => [
                self::DATA => $entity->user
            ]
        ];
    }
}
