<?php
namespace App\View\Schema;

use App\Model\Entity\EventSeries;
use Cake\Core\Configure;
use JsonApi\View\Schema\EntitySchema;

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
        $baseUrl = Configure::read('mainSiteBaseUrl');

        return [
            'title' => $series->title,
            'url' => $baseUrl . '/event-series/' . $series->id,
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param EventSeries $series Entity
     * @param bool $isPrimary Is primary flag
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($series, bool $isPrimary, array $includeRelationships): ?array
    {
        return [
            'events' => [
                self::DATA => $series->events,
            ],
            'user' => [
                self::DATA => $series->user,
            ],
        ];
    }
}
