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
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Text;
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
        $this->loadComponent('Calendar.Calendar');
        $this->RequestHandler->setConfig('viewClassMap', ['ics' => 'Calendar.Ical']);
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

        if ($this->request->getParam('_ext') == 'ics') {
            $filename = sprintf('%s-%s.ics', Text::slug($event->title), date('m-d-Y', strtotime($event->date)));
            $this->response = $this->response->withDownload($filename);
        }

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
            ->find()
            ->where(['date' => $date])
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
        // Create an entity for passing back to the view
        $event = new Event();

        if ($this->request->is('post')) {
            /* Update the entity that will be shown in the view.
             * Note that the EventForm class handles database updates and doesn't make use of this entity.  */
            $data = $this->request->getData();

            /* For validation, replace the date string with a FrozenDate object for only the first date.
             * The selected date(s) will be accessed in the view directly from the request object. */
            $dateDelimiter = '; ';
            $dates = explode($dateDelimiter, $this->request->getData('date'));
            $data['date'] = new FrozenDate($dates[0]);

            $timeStart = $this->request->getData('time_start');
            $timeEnd = $this->request->getData('time_end');
            $data['time_start'] = $timeStart ? new FrozenTime($timeStart) : null;
            $data['time_end'] = $timeEnd ? new FrozenTime($timeEnd) : null;
            $event = $this->Events->patchEntity($event, $data);
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
        $dates = explode('; ', $this->request->getData('date'));
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
                $seriesTitle = $this->request->getData('event_series.title');
                $addedEvents = $eventForm->addEventSeries($addedEvents, $seriesTitle, $this->Auth->user());
            }
        } catch (BadRequestException $e) {
            $errors = $eventForm->getErrors();
            if ($errors) {
                foreach ($errors as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        if (is_string($error)) {
                            $this->Flash->error($error);
                        } else {
                            foreach (array_values(Hash::flatten($error)) as $err) {
                                $this->Flash->error($err);
                            }
                        }
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

        $eventsCount = count($addedEvents);
        /** @var Event $firstEvent */
        $firstEvent = array_shift($addedEvents);
        if ($firstEvent->published) {
            $this->Flash->success(__n('Event', 'Events', $eventsCount) . ' added and published');

            return $this->redirect([
                'controller' => 'Events',
                'action' => 'view',
                'id' => $firstEvent->id,
            ]);
        } else {
            $this->Flash->success(sprintf(
                '%s submitted for review. Once %s approved by an administrator, %s will appear on the calendar. ' .
                'Once you have an approved event, this will happen automatically as long as you\'re logged in.',
                __n('Event', 'Events', $eventsCount),
                __n('it\'s', 'they\'re', $eventsCount),
                __n('it will', 'they will', $eventsCount)
            ));

            return $this->redirect([
                'controller' => 'Events',
                'action' => 'index',
            ]);
        }
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

        // "Render" determines whether the code is rendered
        $renderSeriesNameRow = $action == 'add' || $event->series_id;

        // "Show" determines whether the element is made initially visible with CSS
        $date = $this->request->getData('date');
        $dateDelimiter = '; ';
        $preselectedDates = $date ? explode($dateDelimiter, $date) : [];
        $hasMultipleDates = count($preselectedDates) > 1;
        $showSeriesNameRow = $event->series_id || $hasMultipleDates;

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
            'renderSeriesNameRow',
            'showSeriesNameRow'
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
     * @param int|null $eventId The ID of an event
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

        $event = $this->Events->get($eventId, [
            'contain' => ['Images', 'Tags', 'EventSeries'],
        ]);

        // Check user permission
        if (!$this->userCanEdit($event)) {
            throw new ForbiddenException('You don\'t have permission to edit that event');
        }

        // Prepare form
        $this->setEventFormVars($event);
        $this->set(['pageTitle' => 'Edit ' . $event->title]);

        if (!$this->request->is(['patch', 'post', 'put'])) {
            return $this->render('form');
        }

        $data = $this->request->getData() + [
                'images' => [],
                'tag_ids' => [],
                'tag_names' => [],
                'time_end' => null,
            ];
        if (!$event->series_id) {
            unset($data['EventSeries']);
        }

        $event = $this->Events->patchEntity($event, $data);
        $user = $this->Auth->user();
        $event->autoApprove($user);
        $event->autoPublish($user);
        $event->processTags($data['tags']['_ids'], $data['customTags']);
        $event->setImageJoinData($data['images']);
        $saved = $this->Events->save($event, ['associated' => ['Images', 'Tags', 'EventSeries']]);
        if ($saved) {
            $this->Flash->success('Event updated');

            return $this->redirect([
                'controller' => 'Events',
                'action' => 'view',
                'id' => $event->id,
            ]);
        }
        $this->Flash->error(
            'The event could not be updated. ' .
            'Please correct any indicated errors and try again, or contact an administrator if you need assistance.'
        );

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

        return $this->redirect($this->referer());
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

    /**
     * Search results page
     *
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function search()
    {
        $this->set([
            'pageTitle' => 'Search Results',
        ]);
        $searchTerm = $this->request->getQuery('q');
        if (!$searchTerm) {
            throw new BadRequestException('Please provide a search term');
        }

        $direction = $this->request->getQuery('direction') ?? 'future';
        $counts = ['future' => 0, 'past' => 0, 'all' => 0];

        // Get search results
        $query = $this->Events->getSearchResultsQuery($searchTerm, $direction);
        $counts[$direction] = $query->count();
        $order = $direction == 'past' ? 'DESC' : 'ASC';
        $query->epilog("ORDER BY Events__date $order, Events__time_start ASC");
        $events = $this->paginate($query);

        if ($direction == 'all') {
            $currentDate = date('Y-m-d');
            foreach ($events as $event) {
                $key = $event->date->format('Y-m-d') >= $currentDate ? 'future' : 'past';
                $counts[$key]++;
            }
        } else {
            // Determine if there are events in the opposite direction
            $otherDirection = ($direction == 'future') ? 'past' : 'future';
            $otherDirectionQuery = $this->Events->getSearchResultsQuery($searchTerm, $otherDirection);
            $counts[$otherDirection] = $otherDirectionQuery->count();
        }
        $this->set([
            'counts' => $counts,
            'direction' => $direction,
            'directionAdjective' => ($direction == 'future') ? 'upcoming' : $direction,
            'events' => $events,
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * Displays a page with this user's events
     *
     * @return void
     */
    public function mine()
    {
        $query = $this->Events
            ->find('ordered', ['direction' => 'DESC'])
            ->where(['user_id' => $this->Auth->user('id')]);

        $events = $this->paginate($query);

        $this->set([
            'events' => $events,
            'pageTitle' => 'My Events',
        ]);
    }
}
