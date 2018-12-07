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
     * @return int
     */
    public function getId($entity)
    {
        return $entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output, setting any falsy values to NULL
     *
     * @param Event $entity Entity
     * @return array
     */
    public function getAttributes($entity)
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $attributes = [
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
            'url' => $baseUrl . '/event/' . $entity->id
        ];

        $simpleAttributes = [
            'title',
            'description',
            'location',
            'location_details',
            'address',
            'time_start',
            'time_end',
            'age_restriction',
            'cost',
            'source'
        ];
        foreach ($simpleAttributes as $field) {
            if (isset($entity->$field)) {
                $attributes[$field] = $entity->$field ? $entity->$field : null;
            }
        }

        if (isset($entity->user)) {
            $attributes['user'] = $entity->user ?
                [
                    'id' => $entity->user->id,
                    'name' => $entity->user->name,
                    'email' => $entity->user->email
                ] : null;
        }

        if (isset($entity->tags)) {
            $attributes['tags'] = [];
            foreach ($entity->tags as $tag) {
                $attributes['tags'][] = [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            }
        }

        if (isset($entity->images)) {
            $attributes['images'] = [];
            foreach ($entity->images as $image) {
                $attributes['images'][] = [
                    'tiny_url' => $baseUrl . '/img/events/tiny/' . $image->filename,
                    'small_url' => $baseUrl . '/img/events/small/' . $image->filename,
                    'full_url' => $baseUrl . '/img/events/full/' . $image->filename,
                    'caption' => $image->caption
                ];
            }
        }

        ksort($attributes);

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
