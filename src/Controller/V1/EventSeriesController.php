<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\EventSeriesTable;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;

/**
 * Class EventSeriesController
 * @package App\Controller\V1
 * @property EventSeriesTable $EventSeries
 */
class EventSeriesController extends ApiController
{
    public $paginate = [
        'Events' => [
            'limit' => 50,
            'order' => [
                'Events.date' => 'desc',
                'Events.time_start' => 'desc',
            ],
            'scope' => 'event'
        ]
    ];

    /**
     * /event-series/{eventSeriesId} endpoint
     *
     * @param int|null $seriesId Event series ID
     * @return void
     * @throws \Exception
     */
    public function view($seriesId = null)
    {
        if (!$seriesId) {
            throw new BadRequestException('Event series ID is required');
        }

        $seriesExists = $this->EventSeries->exists(['id' => $seriesId]);
        if (!$seriesExists) {
            throw new BadRequestException("Series with ID $seriesId not found");
        }

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $query = $eventsTable
            ->find('forApi')
            ->where(['series_id' => $seriesId]);

        $this->loadComponent('ApiPagination', ['model' => 'Events']);
        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User'
            ],
            '_serialize' => ['events'],
            'events' => $this->paginate($query, ['scope' => 'event'])
        ]);
    }
}
