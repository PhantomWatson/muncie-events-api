<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\Event;
use App\Model\Entity\User;
use App\Model\Table\EventsTable;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
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
     * /events endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws \Exception
     */
    public function index()
    {
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
     * @throws \Exception
     */
    public function future()
    {
        $this->loadComponent('ApiPagination');
        $tags = $this->request->getQuery('withTags');
        $query = $this->Events
            ->find('forApi', $this->getFinderOptions())
            ->find('future')
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
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
     * @throws \Exception
     */
    public function search()
    {
        $this->loadComponent('ApiPagination');
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
                'EventSeries',
                'Image',
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
     * @throws \Exception
     */
    public function category($categoryId = null)
    {
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
            ->find('future')
            ->find('inCategory', ['categoryId' => $categoryId])
            ->find('tagged', ['tags' => $tags]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User'
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query)
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
            'images' => []
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
        foreach ($dates as $date) {
            $addedEvents[] = $this->addSingleEvent($data, $date, $this->tokenUser);
        }

        // Associate events with a series, if applicable
        if (count($dates) > 1) {
            $addedEvents = $this->addEventSeries($addedEvents);
        }

        // Send Slack notification
        if (!defined('PHPUNIT_RUNNING') || !PHPUNIT_RUNNING) {
            (new \App\Slack\Slack())->sendNewEventAlert($addedEvents[0]->title);
        }

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User'
            ],
            '_links' => [],
            '_serialize' => ['event'],
            'event' => $addedEvents[0]
        ]);
    }

    /**
     * Processes request data and adds a single event (not connected to a series)
     *
     * @param array $data Request data
     * @param string $date A strtotime parsable date
     * @param User|null $user A user entity, or null if user is anonymous
     * @return Event
     * @throws BadRequestException
     */
    private function addSingleEvent(array $data, $date, $user)
    {
        $data['date'] = new FrozenDate($date);
        foreach (['time_start', 'time_end'] as $timeField) {
            if (!isset($data[$timeField])) {
                continue;
            }
            $data[$timeField] = new FrozenTime($date . ' ' . $data[$timeField], Event::TIMEZONE);
        }
        $event = $this->Events->newEntity($data);
        $event->autoApprove($user);
        $event->autoPublish($user);
        $event->processTags($data['tag_ids'], $data['tag_names']);
        $event->setImageJoinData($data['images']);
        $event->category = $this->Events->Categories->get($event->category_id);
        $event->user = $event->user_id ? $this->Events->Users->get($event->user_id) : null;
        $saved = $this->Events->save($event, [
            'associated' => ['Images', 'Tags']
        ]);
        if (!$saved) {
            $msg = $this->getEventErrorMessage($event);
            throw new BadRequestException($msg);
        }

        return $saved;
    }

    /**
     * Takes an array of events and creates a series to associate them with
     *
     * @param Event[] $events An array of events in this series
     * @return Event[]
     * @throws BadRequestException
     */
    private function addEventSeries(array $events)
    {
        // Create series
        $seriesTable = TableRegistry::getTableLocator()->get('EventSeries');
        $arbitraryEvent = $events[0];
        $series = $seriesTable->newEntity([
            'title' => $arbitraryEvent->title,
            'user_id' => $arbitraryEvent->user_id,
            'published' => $arbitraryEvent->userIsAutoPublishable($arbitraryEvent)
        ]);
        if (!$seriesTable->save($series)) {
            $adminEmail = Configure::read('adminEmail');
            $msg = 'The event could not be submitted. Please correct any errors and try again. If you need ' .
                'assistance, please contact an administrator at ' . $adminEmail . '.';
            throw new BadRequestException($msg);
        }

        // Associate events with the new series
        foreach ($events as &$event) {
            $this->Events->patchEntity($event, ['series_id' => $series->id]);
            $event->event_series = $series;
            if (!$this->Events->save($event)) {
                throw new InternalErrorException('Temporary: Error associating series');
            }
        }

        return $events;
    }

    /**
     * Returns a message to be output to the user for an event with one or more errors
     *
     * @param Event $event Event entity
     * @return string
     */
    private function getEventErrorMessage(Event $event)
    {
        $errors = $event->getErrors();
        if ($errors) {
            $msg = sprintf(
                'Please correct the following %s and try again. ',
                __n('error', 'errors', count($errors))
            );
            foreach ($errors as $field => $fieldErrors) {
                $field = ucwords(str_replace('_', ' ', $field));
                $msg .= "$field: " . implode('; ', $fieldErrors) . '. ';
            }
        } else {
            $msg = 'There was an error submitting this event. ';
        }
        $msg .= sprintf(
            'If you need assistance, please contact an administrator at %s.',
            Configure::read('adminEmail')
        );

        return $msg;
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
        $event = $this->Events->get($eventId);

        // Check user permission
        if (!$this->tokenUser || $event->user_id != $this->tokenUser->id) {
            throw new ForbiddenException('You don\'t have permission to edit that event');
        }

        // Update event
        $data = $this->request->getData();
        $data['date'] = new FrozenDate($data['date']);
        foreach (['time_start', 'time_end'] as $timeField) {
            if (!isset($data[$timeField])) {
                continue;
            }
            $data[$timeField] = new FrozenTime($data['date'] . ' ' . $data[$timeField], Event::TIMEZONE);
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
            ]
        ]);
        $event->processTags($data['tag_ids'] ?? [], $data['tag_names'] ?? []);
        $event->setImageJoinData($data['images'] ?? []);
        $event->category = $this->Events->Categories->get($event->category_id);
        $saved = $this->Events->save($event, [
            'associated' => ['Images', 'Tags']
        ]);
        if (!$saved) {
            $msg = $this->getEventErrorMessage($event);
            throw new BadRequestException($msg);
        }

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User'
            ],
            '_links' => [],
            '_serialize' => ['event'],
            'event' => $event
        ]);
    }
}
