<?php
namespace App\View\Schema;

use App\Model\Entity\Event;
use Cake\Core\Configure;
use JsonApi\View\Schema\EntitySchema;

class EventSchema extends EntitySchema
{
    /**
     * Returns the event's ID
     *
     * @param \Cake\ORM\Entity $entity Event entity
     * @return string
     */
    public function getId($entity): string
    {
        return (string)$entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output, setting any falsy values to NULL
     *
     * @param Event $entity Entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($entity, array $fieldKeysFilter = null): array
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');
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
            'date' => $entity->date->format('Y-m-d'),
            'time_start' => $entity->time_start,
            'time_end' => $entity->time_end,
            'age_restriction' => $entity->age_restriction ? $entity->age_restriction : null,
            'cost' => $entity->cost ? $entity->cost : null,
            'source' => $entity->source ? $entity->source : null,
            'tags' => [],
            'images' => [],
            'url' => $baseUrl . '/event/' . $entity->id
        ];

        foreach ($entity->tags as $tag) {
            $attributes['tags'][] = [
                'id' => $tag->id,
                'name' => $tag->name
            ];
        }

        foreach ($entity->images as $image) {
            $attributes['images'][] = [
                'tiny_url' => $baseUrl . '/img/events/tiny/' . $image->filename,
                'small_url' => $baseUrl . '/img/events/small/' . $image->filename,
                'full_url' => $baseUrl . '/img/events/full/' . $image->filename,
                'caption' => $image->caption
            ];
        }

        return $attributes;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Event $entity Entity
     * @param bool $isPrimary Is primary flag
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($entity, bool $isPrimary, array $includeRelationships): ?array
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
