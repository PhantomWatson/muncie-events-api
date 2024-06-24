<?php
namespace App\View\Schema;

use App\Model\Entity\EventSeries;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class EventSeriesSchema extends EntitySchema
{
    public function getType(): string
    {
        return 'event-series';
    }

    /**
     * Returns the event series's ID
     *
     * @param EventSeries $series EventSeries entity
     * @return string
     */
    public function getId($series): string
    {
        return (string)$series->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param EventSeries $series EventSeries entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($series, array $fieldKeysFilter = null): array
    {
        return self::_getAttributes($series);
    }

    /**
     * @param EventSeries $series
     * @return array
     */
    public static function _getAttributes(EventSeries $series): array
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');

        return [
            'title' => $series->title,
            'url' => $baseUrl . '/event-series/' . $series->id,
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param EventSeries $resource Entity
     * @param ContextInterface $context
     * @return array
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        return [
            'events' => [
                'data' => $resource->events,
            ],
            'user' => [
                'data' => $resource->user,
            ],
        ];
    }
}
