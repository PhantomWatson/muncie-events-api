<?php
namespace App\Controller;

use App\Form\EventForm;
use App\Model\Entity\Category;
use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Model\Table\TagsTable;
use App\Model\Table\UsersTable;
use App\Slack\Slack;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\ResultSetInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;
use Recaptcha\Controller\Component\RecaptchaComponent;

/**
 * Events Controller
 *
 * @property EventsTable $Events
 * @property TagsTable $Tags
 * @property RecaptchaComponent $Recaptcha
 *
 * @method Event[]|ResultSetInterface paginate($object = null, array $settings = [])
 */
class EventsController extends AppController
{
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
            'add',
            'category',
            'day',
            'index',
            'location',
            'tag',
            'view',
        ]);

        $action = $this->request->getParam('action');
        if ($action === 'add') {
            $this->loadComponent('Recaptcha.Recaptcha');
        }
    }

    /**
     * Index method
     *
     * @param string|null $startDate The earliest date to fetch events for
     * @return void
     */
    public function index($startDate = null)
    {
        $pageSize = '1 month';
        $startDate = $startDate ?? date('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $pageSize));
        $events = $this->Events
            ->find('ordered')
            ->find('published')
            ->find('startingOn', ['date' => $startDate])
            ->find('endingOn', ['date' => $endDate])
            ->find('withAllAssociated')
            ->all();
        $this->set([
            'events' => $events,
        ]);

        if ($this->request->getQuery('page')) {
            $this->render('/Events/page');
        }
    }

    /**
     * Displays events in the specified category
     *
     * @param string $slug Slug of category name
     * @return void
     * @throws NotFoundException
     */
    public function category($slug)
    {
        /** @var Category $category */
        $category = $this->Events->Categories
            ->find()
            ->where(['slug' => $slug])
            ->first();
        if (!$category) {
            throw new NotFoundException(sprintf('The "%s" event category was not found', $slug));
        }

        $events = $this->Events
            ->find('future')
            ->find('published')
            ->find('ordered')
            ->find('withAllAssociated')
            ->find('inCategory', ['categoryId' => $category->id]);

        $this->set([
            'category' => $category,
            'events' => $events,
            'pageTitle' => $category->name,
        ]);
    }

    /**
     * Displays events with a specified tag
     *
     * @param string|null $idAndSlug A string formatted as "$id-$slug"
     * @param string|null $direction Either 'upcoming', 'past', or null (defaults to 'upcoming')
     * @return null|Response
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function tag($idAndSlug = '', $direction = null)
    {
        $this->loadModel('Tags');
        $tag = $this->Tags->getFromIdSlug($idAndSlug);
        if (!$tag) {
            throw new NotFoundException('Sorry, we couldn\'t find that tag');
        }

        $direction = $direction ?? 'upcoming';
        if (!in_array($direction, ['upcoming', 'past'])) {
            throw new BadRequestException(
                'Sorry, but due to our current one-dimensional understanding of time, you can\'t view events ' .
                'in any direction other than \'upcoming\' or \'past\'.'
            );
        }

        $baseQuery = $this->Events
            ->find('published')
            ->find('ordered', ['direction' => $direction == 'past' ? 'DESC' : 'ASC'])
            ->find('withAllAssociated')
            ->find('tagged', ['tags' => [$tag->name]]);
        $mainQuery = (clone $baseQuery)->find($direction == 'past' ? 'past' : 'future');
        $oppositeDirectionQuery = (clone $baseQuery)->find($direction == 'past' ? 'future' : 'past');

        $this->set([
            'pageTitle' => 'Tag: ' . ucwords($tag->name),
            'events' => $this->paginate($mainQuery),
            'count' => $mainQuery->count(),
            'direction' => $direction,
            'countOppositeDirection' => $oppositeDirectionQuery->count(),
            'oppositeDirection' => $direction == 'past' ? 'upcoming' : 'past',
            'tag' => $tag,
        ]);

        return null;
    }

    /**
     * Shows a specific event
     *
     * @param string|null|int $id Event id
     * @return void
     * @throws RecordNotFoundException
     */
    public function view($id = null)
    {
        /** @var Event $event */
        $event = $this->Events
            ->find('withAllAssociated')
            ->where(['Events.id' => $id])
            ->firstOrFail();
        $this->set([
            'event' => $event,
            'pageTitle' => $event->title,
        ]);
    }

    /**
     * Shows the events taking place on the specified day
     *
     * @param string $month Two-digit month, optionally zero-padded
     * @param string $day Two-digit day, optionally zero-padded
     * @param string $year Four-digit year
     * @return void
     */
    public function day($month, $day, $year)
    {
        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $date = "$year-$month-$day";
        $events = $this->Events
            ->find('startingOn', compact('date'))
            ->find('endingOn', compact('date'))
            ->find('ordered')
            ->find('published')
            ->find('withAllAssociated')
            ->all();

        $this->set(compact(
            'date',
            'day',
            'events',
            'month',
            'year'
        ));
        $this->set(['pageTitle' => 'Events on ' . date('F j, Y', strtotime($date))]);
    }

    /**
     * Adds a new event
     *
     * @return Response|null
     */
    public function add()
    {
        // Create event entity
        $event = $this->Events->newEntityWithDefaults();
        if ($this->request->is('post')) {
            $event = $this->Events->patchEntity($event, $this->request->getData());
        }

        // Set view variables
        $this->setEventFormVars($event);
        $this->set(['pageTitle' => 'Submit an Event']);

        // Render if the form isn't being submitted
        if ($this->request->is(['get'])) {
            return $this->render('form');
        }

        // Abort on CAPTCHA error
        if (!$this->passedBotDetection()) {
            $this->Flash->error(
                'Spam detection failed. ' .
                'Please try the reCAPTCHA challenge again or log in before submitting an event.'
            );

            return $this->render('form');
        }

        // Add an event(s)
        $eventForm = new EventForm();
        $addedEvents = [];
        $dates = explode(',', $this->request->getData('date'));
        sort($dates);
        $data = $this->request->getData() + [
                'images' => [],
                'tag_ids' => [],
                'tag_names' => [],
                'time_end' => null,
            ];
        try {
            foreach ($dates as $date) {
                $addedEvents[] = $eventForm->addSingleEvent($data, $date, $this->Auth->user());
            }

            // Associate events with a series, if applicable
            if (count($dates) > 1) {
                $addedEvents = $eventForm->addEventSeries($addedEvents);
            }
        } catch (BadRequestException $e) {
            $errors = $eventForm->getErrors();
            if ($errors) {
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $this->Flash->error($error);
                    }
                }
            } else {
                $this->Flash->error($e->getMessage());
            }

            return $this->render('form');
        }

        // Send Slack notification
        $phpUnitRunning = defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING;
        if ($addedEvents && !$phpUnitRunning) {
            (new Slack())->sendNewEventAlert($addedEvents[0]->title);
        }

        return $this->render('form');
    }

    /**
     * Sets view variables used in the event form
     *
     * @param Event $event Event entity
     * @return void
     */
    private function setEventFormVars($event)
    {
        /**
         * @var EventsTable $eventsTable
         * @var UsersTable $usersTable
         */
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $userId = $this->Auth->user('id');
        $autoPublish = $usersTable->getAutoPublish($userId);
        $action = $this->request->getParam('action');
        $multipleDatesAllowed = in_array($action, ['add', 'editSeries']);
        $firstEvent = isset($autoPublish) && !$autoPublish && $action == 'add';
        $date = $this->request->getData('date');
        $preselectedDates = $date ? explode(',', $date) : [];
        $defaultDate = 0; // Today
        $hasEndTime = (bool)$event->time_end;
        $hasAddress = (bool)$event->address;
        $hasCost = (bool)$event->cost;
        $hasAges = (bool)$event->age_restriction;
        $hasSource = (bool)$event->source;
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $categories = $categoriesTable->find('list')->orderAsc('weight');
        $autocompleteLocations = [];
        $locations = $this->Events
            ->find('locations')
            ->enableHydration(false)
            ->toArray();
        foreach ($locations as $location) {
            $autocompleteLocations[] = [
                'label' => $location['location'],
                'value' => $location['address'],
            ];
        }
        $uploadMax = ini_get('upload_max_filesize');
        $postMax = ini_get('post_max_size');
        $serverFilesizeLimit = min($uploadMax, $postMax);
        $manualFilesizeLimit = '10M';
        $filesizeLimit = min($manualFilesizeLimit, $serverFilesizeLimit);

        $this->set(compact(
            'action',
            'autocompleteLocations',
            'autoPublish',
            'categories',
            'defaultDate',
            'event',
            'filesizeLimit',
            'firstEvent',
            'hasAddress',
            'hasAges',
            'hasCost',
            'hasEndTime',
            'hasSource',
            'multipleDatesAllowed',
            'preselectedDates'
        ));
    }

    /**
     * Returns a boolean indicating whether or not the current user has passed bot detection
     *
     * @return bool
     */
    private function passedBotDetection()
    {
        return php_sapi_name() == 'cli' || $this->Auth->user() || $this->Recaptcha->verify();
    }

    /**
     * Edits an event
     *
     * @param int $eventId The ID of an event
     * @return Response
     * @throws ForbiddenException
     */
    public function edit($eventId = null)
    {
        // Get event
        $eventExists = $this->Events->exists(['id' => $eventId]);
        if (!$eventExists) {
            throw new BadRequestException("Event with ID $eventId not found");
        }

        /** @var Event $event */
        $event = $this->Events->get($eventId, [
            'contain' => ['Images', 'Tags'],
        ]);

        // Check user permission
        if (!$this->userCanEdit($event)) {
            throw new ForbiddenException('You don\'t have permission to edit that event');
        }

        // Prepare form
        $this->setEventFormVars($event);
        $this->set(['pageTitle' => 'Edit Event']);

        if (!$this->request->is(['patch', 'post', 'put'])) {
            return $this->render('form');
        }

        $data = $this->request->getData() + [
                'images' => [],
                'tag_ids' => [],
                'tag_names' => [],
                'time_end' => null,
            ];

        $event = $this->Events->patchEntity($event, $data);
        $user = $this->Auth->user();
        $event->autoApprove($user);
        $event->autoPublish($user);
        $event->processTags($data['tag_ids'], $data['tag_names']);
        $saved = $this->Events->save($event, ['associated' => ['Images', 'Tags']]);
        if ($saved) {
            $this->Flash->success('Event updated');

            return $this->redirect([
                'controller' => 'Events',
                'action' => 'view',
                'id' => $event->id,
            ]);
        }
        $msg = 'The event could not be updated. Please correct any indicated errors and try again, or contact an ' .
            'administrator if you need assistance.';
        $this->Flash->error($msg);

        return $this->render('form');
    }

    /**
     * Returns TRUE if the current user can edit the specified event
     *
     * @param Event $event Event entity
     * @return bool
     */
    private function userCanEdit(Event $event)
    {
        // Anonymous users may not edit
        if (!$this->Auth->user()) {
            return false;
        }

        // An event's author may edit
        if ($event->user_id != $this->Auth->user('id')) {
            return true;
        }

        // Any admin may edit
        return $this->Auth->user('role') == 'admin';
    }

    /**
     * Deletes an event
     *
     * @param int|null $eventId Event ID
     * @return Response
     */
    public function delete($eventId = null)
    {
        $event = $this->Events->get($eventId);
        if (!$this->userCanEdit($event)) {
            $this->Flash->error('You are not authorized to delete that event');

            return $this->redirect($this->referer());
        }

        if ($this->Events->delete($event)) {
            $this->Flash->success('The event has been deleted.');

            return $this->redirect('/');
        }
        $this->Flash->error(
            'The event could not be deleted. Please try again or contact an administrator for assistance.'
        );

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Redirects to the /events/day page for today
     *
     * @return Response
     */
    public function today()
    {
        $timestamp = time();

        return $this->redirect([
            'controller' => 'Events',
            'action' => 'day',
            date('m', $timestamp),
            date('d', $timestamp),
            date('Y', $timestamp),
        ]);
    }

    /**
     * Redirects to the /events/day page for tomorrow
     *
     * @return Response
     */
    public function tomorrow()
    {
        $timestamp = strtotime('+1 day');

        return $this->redirect([
            'controller' => 'Events',
            'action' => 'day',
            date('m', $timestamp),
            date('d', $timestamp),
            date('Y', $timestamp),
        ]);
    }

    /**
     * Page for viewing events taking place at a given location
     *
     * @param null $locationSlug The slug of the location name
     * @param string $direction Either 'future' or 'past' (leave blank for 'future')
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function location($locationSlug = null, $direction = 'future')
    {
        if (!$locationSlug) {
            throw new BadRequestException('Error: No location name given.');
        }

        if (!in_array($direction, ['future', 'past'])) {
            throw new BadRequestException(
                'Direction not recognized. Either "future" or "past" expected. ' .
                'Your weird Time Lord stuff won\'t work on us.'
            );
        }

        // For this page's results
        $primaryQuery = $this->Events
            ->find('published')
            ->find('withAllAssociated')
            ->find('ordered')
            ->find('atLocation', ['location_slug' => $locationSlug])
            ->find($direction);
        $count = $primaryQuery->count();
        $events = $this->paginate($primaryQuery);

        // For finding the count of results in the other (past/future) direction
        $secondaryQuery = $this->Events
            ->find('published')
            ->find('atLocation', ['location_slug' => $locationSlug])
            ->find($direction == 'future' ? 'past' : 'future');
        $countOtherDirection = $secondaryQuery->count();

        $locationName = $this->Events->getFullLocationName($locationSlug);
        $pageTitle = $locationName == Event::VIRTUAL_LOCATION ? 'Virtual Events' : $locationSlug;

        $this->set([
            'count' => $count,
            'countOtherDirection' => $countOtherDirection,
            'direction' => $direction,
            'events' => $events,
            'locationName' => $locationName,
            'locationSlug' => $locationSlug,
            'pageTitle' => $pageTitle,
        ]);
    }

    /**
     * Lists all of the locations at which past events took place
     *
     * @return void
     */
    public function locationsPast()
    {
        $locations = $this->Events
            ->find('locations')
            ->find('past')
            ->enableHydration(false)
            ->toArray();
        $count = count($locations);

        $this->set([
            'count' => $count,
            'locations' => $locations,
            'pageTitle' => 'Locations of Past Events',
        ]);
    }
}
