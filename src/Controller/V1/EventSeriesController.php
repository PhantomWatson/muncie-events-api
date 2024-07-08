<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\EventSeries;
use App\Model\Table\EventSeriesTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Class EventSeriesController
 * @package App\Controller\V1
 * @property \App\Model\Table\EventSeriesTable $EventSeries
 */
class EventSeriesController extends ApiController
{
    public array $paginate = [
        'Events' => [
            'limit' => 50,
            'order' => [
                'Events.date' => 'desc',
                'Events.time_start' => 'desc',
            ],
            'scope' => 'event',
        ],
    ];

    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow(['view']);
    }

    /**
     * /event-series/{eventSeriesId} endpoint
     *
     * @param int|null $seriesId Event series ID
     * @return void
     * @throws Exception
     */
    public function view($seriesId = null)
    {
        if (!$seriesId) {
            throw new BadRequestException('Event series ID is required');
        }

        $this->request->allowMethod('get');

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
                'User',
            ],
            '_serialize' => ['events'],
            'events' => $this->paginate($query, ['scope' => 'event']),
        ]);
    }

    /**
     * DELETE /v1/event-series/{seriesId} endpoint
     *
     * @param int|null $eventSeriesId EventSeries ID
     * @return void
     * @throws InternalErrorException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function delete($eventSeriesId = null)
    {
        $this->request->allowMethod('delete');

        // Get event
        $seriesExists = $this->EventSeries->exists(['id' => $eventSeriesId]);
        if (!$seriesExists) {
            throw new BadRequestException("Series with ID $eventSeriesId not found");
        }
        /** @var EventSeries $series */
        $series = $this->EventSeries->get($eventSeriesId);

        // Check user permission
        if (!$this->tokenUser || $series->user_id != $this->tokenUser->id) {
            throw new ForbiddenException('You don\'t have permission to remove that series');
        }

        if (!$this->EventSeries->delete($series)) {
            throw new InternalErrorException(
                'That series could not be deleted. Please try again or contact an administrator for assistance.'
            );
        }

        $this->set204Response();
    }
}
