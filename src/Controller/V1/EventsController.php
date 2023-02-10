<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Form\EventForm;
use App\Model\Table\EventsTable;
use App\Slack\Slack;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Exception;

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
        ],
    ];

    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'category',
            'upcoming',
            'index',
            'search',
            'view',
        ]);
    }

    /**
     * /events endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws Exception
     */
    public function index()
    {
        $this->request->allowMethod('get');

        $this->loadComponent('ApiPagination');
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
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['events'],
            'events' => $this->paginate($query),
        ]);
    }

    /**
     * /events/upcoming endpoint
     *
     * @return void
     * @throws Exception
     */
    public function upcoming()
    {
        $this->request->allowMethod('get');

        $this->loadComponent('ApiPagination');
        $tags = $this->request->getQuery('withTags');
        $query = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find('upcoming')
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query),
        ]);
    }

    /**
     * /events/search endpoint
     *
     * @param string|null $direction Either null or "past"
     * @return void
     * @throws BadRequestException
     * @throws Exception
     */
    public function search($direction = null)
    {
        $this->request->allowMethod('get');

        if (!in_array($direction, [null, 'past'])) {
            throw new BadRequestException(
                "Unrecognized direction: \"$direction\". This must either be \"past\" or left blank."
            );
        }

        $this->loadComponent('ApiPagination');
        $search = $this->request->getQuery('q');
        $search = trim($search);
        if (!$search) {
            throw new BadRequestException('The parameter "q" is required');
        }

        $categoryId = $this->request->getQuery('category');
        if ($categoryId) {
            $categoryExists = TableRegistry::getTableLocator()
                ->get('Categories')
                ->exists(['id' => $categoryId]);
            if (!$categoryExists) {
                throw new BadRequestException("Category with ID $categoryId not found");
            }
        }

        $baseQuery = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find($direction ?? 'upcoming');
        if ($categoryId) {
            $baseQuery->where(['category_id' => $categoryId]);
        }
        $matchesEventDetails = $baseQuery->cleanCopy()
            ->find('search', ['search' => $this->request->getQueryParams()]);
        $matchesTag = $baseQuery->cleanCopy()
            ->find('tagged', ['tags' => [$search]]);
        $finalQuery = $matchesEventDetails->union($matchesTag);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($finalQuery),
        ]);
    }

    /**
     * /events/category endpoint
     *
     * @param int|null $categoryId Category ID
     * @return void
     * @throws BadRequestException
     * @throws Exception
     */
    public function category($categoryId = null)
    {
        $this->request->allowMethod('get');

        $this->loadComponent('ApiPagination');
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
            ->find('upcoming')
            ->find('inCategory', ['categoryId' => $categoryId])
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query),
        ]);
    }

    /**
     * GET /event/{eventID} endpoint
     *
     * @param int|null $eventId Event ID
     * @return void
     */
    public function view($eventId = null)
    {
        $this->request->allowMethod('get');

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
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['event'],
            'event' => $event,
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

    /**
     * POST /event endpoint
     *
     * @return void
     * @throws BadRequestException
     */
    public function add()
    {
        $this->request->allowMethod('post');

        $data = $this->request->getData();
        $data['user_id'] = $this->tokenUser ? $this->tokenUser->id : null;
        $data['published'] = false;
        $data['approved_by'] = null;

        // Add blank values for missing keys
        $optionalFields = [
            'location_details' => '',
            'address' => '',
            'cost' => '',
            'age_restriction' => '',
            'source' => '',
            'tag_ids' => [],
            'tag_names' => [],
            'images' => [],
        ];
        foreach ($optionalFields as $optionalField => $blankValue) {
            if (!isset($data[$optionalField])) {
                $data[$optionalField] = $blankValue;
            }
        }

        // Normalize 'date' string/array to an array
        $dates = $this->request->getData('date');
        if (!$dates) {
            throw new BadRequestException('No date specified');
        }
        if (!is_array($dates)) {
            $dates = [$dates];
        }

        // Add event(s)
        $addedEvents = [];
        sort($dates);
        $eventForm = new EventForm();
        foreach ($dates as $date) {
            $addedEvents[] = $eventForm->addSingleEvent($data, $date, $this->tokenUser);
        }

        // Associate events with a series, if applicable
        if (count($dates) > 1) {
            $seriesTitle = $data['title'];
            $addedEvents = $eventForm->addEventSeries($addedEvents, $seriesTitle, $this->tokenUser);
        }

        // Send Slack notification
        if (!defined('PHPUNIT_RUNNING') || !PHPUNIT_RUNNING) {
            (new Slack())->sendNewEventAlert($addedEvents[0]->title);
        }

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_links' => [],
            '_serialize' => ['event'],
            'event' => $addedEvents[0],
        ]);
    }

    /**
     * PATCH /event/{eventId} endpoint
     *
     * @param int|null $eventId Event ID
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function edit($eventId = null)
    {
        $this->request->allowMethod('patch');

        // Get event
        $eventExists = $this->Events->exists(['id' => $eventId]);
        if (!$eventExists) {
            throw new BadRequestException("Event with ID $eventId not found");
        }
        $event = $this->Events->get($eventId, [
            'contain' => ['Categories', 'EventSeries', 'Images', 'Tags', 'Users'],
        ]);

        // Check user permission
        if (!$this->tokenUser || $event->user_id != $this->tokenUser->id) {
            throw new ForbiddenException('You don\'t have permission to edit that event');
        }

        // Throw exception if any protected fields are in request data
        $data = $this->request->getData();
        foreach ($event->updateProtectedFields as $protectedField) {
            if (isset($data[$protectedField])) {
                throw new BadRequestException("The $protectedField field is not allowed");
            }
        }

        // Update event
        $eventForm = new EventForm();
        if (isset($data['date'])) {
            if (!is_string($data['date'])) {
                throw new BadRequestException(sprintf(
                    "Error: Date must be passed as a string when editing an event (%s provided)",
                    gettype($data['date'])
                ));
            }
            $data['date'] = $eventForm->parseDate($data['date']);
        }
        foreach (['time_start', 'time_end'] as $timeField) {
            if (!isset($data[$timeField])) {
                continue;
            }
            $data[$timeField] = $eventForm->parseTime($data['date'], $data[$timeField]);
        }
        $this->Events->patchEntity($event, $data, [
            'fields' => [
                'title',
                'description',
                'location',
                'location_details',
                'address',
                'category_id',
                'date',
                'time_start',
                'time_end',
                'age_restriction',
                'cost',
                'source',
            ],
        ]);
        $event->processTags($data['tag_ids'] ?? [], $data['tag_names'] ?? []);
        $event->setImageJoinData($data['images'] ?? []);
        $event->setLocationSlug();
        $event->category = $this->Events->Categories->get($event->category_id);
        $saved = $this->Events->save($event, [
            'associated' => ['Images', 'Tags'],
        ]);
        if (!$saved) {
            $msg = $eventForm->getEventErrorMessage($event);
            throw new BadRequestException($msg);
        }

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_links' => [],
            '_serialize' => ['event'],
            'event' => $event,
        ]);
    }

    /**
     * DELETE /event/{eventId} endpoint
     *
     * @param int $eventId Event ID
     * @return void
     * @throws InternalErrorException
     * @throws ForbiddenException
     * @throws BadRequestException
     */
    public function delete($eventId = null)
    {
        $this->request->allowMethod('delete');

        $exists = $this->Events->exists(['id' => $eventId]);
        if (!$exists) {
            throw new BadRequestException(
                'The selected event could not be found, possibly because it has already been deleted.'
            );
        }

        $event = $this->Events->get($eventId);

        // Check user permission
        if (!$this->tokenUser || $event->user_id != $this->tokenUser->id) {
            throw new ForbiddenException('You don\'t have permission to delete that event');
        }

        if (!$this->Events->delete($event)) {
            throw new InternalErrorException(
                'The event could not be deleted. Please try again. Or contact an administrator for assistance.'
            );
        }

        $this->set204Response();
    }
}
