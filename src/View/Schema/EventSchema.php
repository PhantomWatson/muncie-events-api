<?php
namespace App\View\Schema;

use App\Model\Entity\Event;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Exception;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class EventSchema extends EntitySchema
{
    protected $selfSubUrl = '/event';

    public function getType(): string
    {
        return 'events';
    }

    /**
     * Returns the event's ID
     *
     * @param Entity $entity Event entity
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
     * @throws Exception
     */
    public function getAttributes($entity, $fieldKeysFilter = null): array
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $entity->category->noEventCount = true;

        $attributes = [
            'title' => $entity->title,
            'description' => $entity->description,
            'location' => $entity->location,
            'location_details' => $entity->location_details ? $entity->location_details : null,
            'address' => $entity->address ? $entity->address : null,
            'user' => $entity->user ?
                [
                    'name' => $entity->user->name,
                    'email' => $entity->user->email,
                ] : null,
            'category' => CategorySchema::_getAttributes($entity->category),
            'series' => $entity->event_series ? EventSeriesSchema::_getAttributes($entity->event_series) : null,
            'date' => $entity->date->format('Y-m-d'),
            'time_start' => $entity->time_start->toRfc3339String(),
            'time_end' => $entity->time_end->toRfc3339String(),
            'age_restriction' => $entity->age_restriction ? $entity->age_restriction : null,
            'cost' => $entity->cost ? $entity->cost : null,
            'source' => $entity->source ? $entity->source : null,
            'tags' => [],
            'images' => [],
            'url' => $baseUrl . '/event/' . $entity->id,
            'published' => $entity->published,
        ];

        foreach ($entity->tags as $tag) {
            $attributes['tags'][] = [
                'name' => $tag->name,
            ];
        }

        foreach ($entity->images as $image) {
            $attributes['images'][] = [
                'tiny_url' => $baseUrl . '/img/events/tiny/' . $image->filename,
                'small_url' => $baseUrl . '/img/events/small/' . $image->filename,
                'full_url' => $baseUrl . '/img/events/full/' . $image->filename,
                'caption' => $image->_joinData->caption,
            ];
        }

        return $attributes;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Event $resource Entity
     * @param ContextInterface $context
     * @return array
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        return [
            'category' => [
                'data' => $resource->category,
            ],
            'series' => [
                'data' => $resource->event_series,
            ],
            'tags' => [
                'data' => $resource->tags,
            ],
            'user' => [
                'data' => $resource->user,
            ],
        ];
    }
}
