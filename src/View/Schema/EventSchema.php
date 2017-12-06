<?php
namespace App\View\Schema;

use Cake\Routing\Router;
use JsonApi\View\Schema\EntitySchema;

class EventSchema extends EntitySchema
{
    /**
     * Returns the event's ID
     *
     * @param \Cake\ORM\Entity $entity Event entity
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
        $attributes = [
            'title' => $entity->title,
            'description' => $entity->description,
            'location' => $entity->location,
            'location_details' => $entity->location_details ? $entity->location_details : null,
            'address' => $entity->address ? $entity->address : null,
            'user' => $entity->user ?
                [
                    'id' => $entity->user->id,
                    'name' => $entity->user->name,
                    'email' => $entity->user->email
                ] : null,
            'category' => [
                'id' => $entity->category->id,
                'name' => $entity->category->name
            ],
            'series' => $entity->event_series ?
                [
                    'id' => $entity->event_series->id,
                    'title' => $entity->event_series->title
                ] : null,
            'date' => $entity->date,
            'time_start' => $entity->time_start,
            'time_end' => $entity->time_end,
            'age_restriction' => $entity->age_restriction ? $entity->age_restriction : null,
            'cost' => $entity->cost ? $entity->cost : null,
            'source' => $entity->source ? $entity->source : null,
            'tags' => [],
            'url' => 'https://muncieevents.com/event/' . $entity->id
        ];

        foreach ($entity->tags as $tag) {
            $attributes['tags'][] = [
                'id' => $tag->id,
                'name' => $tag->name
            ];
        }

        return $attributes;
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
            'category' => [
                self::DATA => $entity->category
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
