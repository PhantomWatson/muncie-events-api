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
     * @param Entity $resource Event entity
     * @return string
     */
    public function getId($res♠ource): string
    {
        return (string)$resource->get('id');
    }

    /**
     * Returns the attributes for this entity for API output, setting any falsy values to NULL
     *
     * @param Event $resource Entity
     * @param array|ContextInterface|null $context Field keys filter
     * @return array
     * @throws Exception
     */
    public function getAttributes($resource, array|ContextInterface $context = null): array
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $resource->category->noEventCount = true;

        $attributes = [
            'title' => $resource->title,
            'description' => $resource->description,
            'location' => $resource->location,
            'location_details' => $resource->location_details ? $resource->location_details : null,
            'address' => $resource->address ? $resource->address : null,
            'user' => $resource->user ?
                [
                    'name' => $resource->user->name,
                    'email' => $resource->user->email,
                ] : null,
            'category' => CategorySchema::_getAttributes($resource->category),
            'series' => $resource->event_series ? EventSeriesSchema::_getAttributes($resource->event_series) : null,
            'date' => $resource->date->format('Y-m-d'),
            'time_start' => Event::getDatetime($resource->date, $resource->time_start),
            'time_end' => Event::getDatetime($resource->date, $resource->time_end),
            'age_restriction' => $resource->age_restriction ? $resource->age_restriction : null,
            'cost' => $resource->cost ? $resource->cost : null,
            'source' => $resource->source ? $resource->source : null,
            'tags' => [],
            'images' => [],
            'url' => $baseUrl . '/event/' . $resource->id,
            'published' => $resource->published,
        ];

        foreach ($resource->tags as $tag) {
            $attributes['tags'][] = [
                'name' => $tag->name,
            ];
        }

        foreach ($resource->images as $image) {
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
