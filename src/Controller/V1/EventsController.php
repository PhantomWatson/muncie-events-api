<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\EventsTable;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;

/**
 * Class EventsController
 * @package App\Controller\V1
 * @property EventsTable $Events
 */
class EventsController extends ApiController
{
    public $paginate = [
        'limit' => 50,
        'order' => [
            'Events.date' => 'asc',
            'Events.time_start' => 'asc',
        ]
    ];

    /**
     * Initialize method
     *
     * @return \Cake\Http\Response|null
     * @throws \Exception
     * @throws BadRequestException
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('ApiPagination');

        return null;
    }

    /**
     * /events endpoint
     *
     * @return void
     * @throws BadRequestException
     */
    public function index()
    {
        $start = $this->request->getQuery('start');
        $end = $this->request->getQuery('end');
        $tags = $this->request->getQuery('withTags');
        if (!$start) {
            throw new BadRequestException('The parameter "start" is required');
        }

        $query = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find('startingOn', ['date' => $start])
            ->find('endingOn', ['date' => $end])
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events'],
            'events' => $this->paginate($query)
        ]);
    }

    /**
     * /events/future endpoint
     *
     * @return void
     */
    public function future()
    {
        $tags = $this->request->getQuery('withTags');
        $query = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find('future')
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query)
        ]);
    }

    /**
     * /events/search endpoint
     *
     * @return void
     * @throws BadRequestException
     */
    public function search()
    {
        $search = $this->request->getQuery('q');
        $search = trim($search);
        if (!$search) {
            throw new BadRequestException('The parameter "q" is required');
        }

        $baseQuery = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find('future');
        $matchesEventDetails = $baseQuery->cleanCopy()
            ->find('search', ['search' => $this->request->getQueryParams()]);
        $matchesTag = $baseQuery->cleanCopy()
            ->find('tagged', ['tags' => [$search]]);
        $finalQuery = $matchesEventDetails->union($matchesTag);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($finalQuery)
        ]);
    }

    /**
     * /events/category endpoint
     *
     * @param int|null $categoryId Category ID
     * @return void
     * @throws BadRequestException
     */
    public function category($categoryId = null)
    {
        if (!$categoryId) {
            throw new BadRequestException('Category ID is required');
        }

        $categoryExists = TableRegistry::getTableLocator()
            ->get('Categories')
            ->exists(['id' => $categoryId]);
        if (!$categoryExists) {
            throw new BadRequestException("Category with ID $categoryId not found");
        }

        $tags = $this->request->getQuery('withTags');
        $query = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find('future')
            ->find('inCategory', ['categoryId' => $categoryId])
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query)
        ]);
    }

    /**
     * /event/{eventID} endpoint
     *
     * @param int|null $eventId Event ID
     * @return void
     */
    public function view($eventId = null)
    {
        if (!$eventId) {
            throw new BadRequestException('Event ID is required');
        }

        $eventExists = $this->Events->exists(['id' => $eventId]);
        if (!$eventExists) {
            throw new BadRequestException("Event with ID $eventId not found");
        }

        $event = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->where(['Events.id' => $eventId])
            ->first();

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['event'],
            'event' => $event
        ]);
    }

    /**
     * Returns an array of options for the main 'forApi' finder, based on request data
     *
     * @return array
     */
    private function getFinderOptions()
    {
        return [];
    }
}
