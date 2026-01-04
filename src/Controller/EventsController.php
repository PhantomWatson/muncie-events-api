<?php
namespace App\Controller;

use App\Application;
use App\Form\EventForm;
use App\Model\Entity\Category;
use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Model\Table\TagsTable;
use App\Model\Table\UsersTable;
use App\Slack\Slack;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\ResultSetInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query\SelectQuery;
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
    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow([
            'add',
            'category',
            'day',
            'feed',
            'feeds',
            'firstThursday',
            'index',
            'location',
            'locationsPast',
            'search',
            'tag',
            'today',
            'tomorrow',
            'view',
        ]);

        $action = $this->request->getParam('action');
        if ($action === 'add') {
            $this->loadRecaptcha();
        }
        $this->loadComponent('Calendar.Calendar');
        $this->RequestHandler->setConfig('viewClassMap', ['ics' => 'Calendar.Ical']);

        $this->Events = self::fetchTable('Events');
    }

    /**
     * Index method
     *
     * @param string|null $startDate The earliest date to fetch events for
     * @return void
     */
    public function index($startDate = null)
    {
        $minEventCount = 30;
        $timezone = Configure::read('localTimezone');
        $defaultStartDate = (new FrozenTime('now', $timezone))->format('Y-m-d');
        $startDate = $startDate ?? $defaultStartDate;

        // Get minimum number of events
        $events = $this->Events
            ->find('ordered')
            ->find('published')
            ->find('startingOn', ['date' => $startDate])
            ->find('withAllAssociated')
            ->limit($minEventCount)
            ->all()
            ->toArray();

        // Get the rest of the events in the last date of this group
        if ($events) {
            /** @var Event $event */
            $lastEvent = $events[count($events) - 1];
            $lastDate = $lastEvent->date;
            $eventIds = Hash::extract($events, '{n}.id');
            $moreEvents = $this->Events
                ->find('ordered')
                ->find('published')
                ->find('on', ['date' => $lastDate->format('Y-m-d')])
                ->find('withAllAssociated')
                ->where(function (QueryExpression $exp) use ($eventIds) {
                    return $exp->notIn('Events.id', $eventIds);
                })
                ->all()
                ->toArray();
            $events = array_merge($events, $moreEvents);
        }

        $this->set(['events' => $events]);

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
            ->find('upcoming')
            ->find('published')
            ->find('ordered')
            ->find('withAllAssociated')
            ->find('inCategory', ['categoryId' => $category->id])
            ->toArray();

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
        // If request uses underscored URL, respond with redirect to dashed URL
        if (strpos($idAndSlug, '_') !== false && strpos($idAndSlug, '-') === false) {
            $correctedIdAndSlug = str_replace('_', '-', $idAndSlug);

            return $this->redirect([
                'slug' => $correctedIdAndSlug,
                'direction' => $direction,
            ]);
        }

        /** @var TagsTable $tagsTable */
        $tagsTable = $this->fetchTable('Tags');
        $tag = $tagsTable->getFromIdSlug($idAndSlug);
        if (!$tag) {
            throw new NotFoundException('Sorry, we couldn\'t find that tag');
        }

        $direction = $direction ?? 'upcoming';
        if (!in_array($direction, ['upcoming', 'past'])) {
            $direction = 'upcoming';
        }

        $baseQuery = $this->Events
            ->find('published')
            ->find('ordered', ['direction' => $direction == 'past' ? 'DESC' : 'ASC'])
            ->find('withAllAssociated')
            ->find('tagged', ['tags' => [$tag->name]]);
        $mainQuery = (clone $baseQuery)->find($direction);
        $oppositeDirection = Application::oppositeDirection($direction);
        $oppositeDirectionQuery = (clone $baseQuery)->find($oppositeDirection);

        $this->set([
            'pageTitle' => 'Tag: ' . ucwords($tag->name),
            'events' => $this->paginate($mainQuery)->toArray(),
            'count' => $mainQuery->count(),
            'direction' => $direction,
            'countOppositeDirection' => $oppositeDirectionQuery->count(),
            'oppositeDirection' => $oppositeDirection,
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

        $baseUrl = Configure::read('eventImageBaseUrl');
        $ogImages = [];
        foreach ($event->images as $image) {
            $ogImages[] = $baseUrl . 'full/' . $image->filename;
        }

        $this->set([
            'event' => $event,
            'pageTitle' => $event->title,
            'ogMetaTags' => [
                'og:title' => $event->title . ' - ' . $event->date->format('M j, Y'),
                'og:type' => 'article',
                'og:image' => $ogImages,
                'og:url' => $this->request->getUri(),
                'og:site_name' => 'Muncie Events',
                'fb:app_id' => '496726620385625',
                'og:description' => $event->description,
                'article:author' => $event->user->name ?? 'Anonymous',
            ],
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
            ->all()
            ->toArray();

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
        $event = $this->Events->newEmptyEntity();

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
            ->find('published')
            ->select(['location', 'address', 'created'])
            ->distinct(['location', 'address', 'created'])
            ->order([
                'location' => 'ASC',
                'created' => 'DESC',
            ])
            ->enableHydration(false)
            ->toArray();
        foreach ($locations as $location) {
            if (isset($autocompleteLocations[$location['location']])) {
                continue;
            }
            $autocompleteLocations[$location['location']] = [
                'label' => $location['location'],
                'value' => $location['address'],
            ];
        }
        $autocompleteLocations = array_values($autocompleteLocations);
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
        $tagIds = $data['tags']['_ids'] ?? [];
        $event->processTags($tagIds, $data['customTags']);
        $event->setImageJoinData($data['images']);
        $event->setLocationSlug();
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
        if ($event->user_id == $this->Auth->user('id')) {
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
            $redirectUrl = $this->request->getQuery('redirect');
            $redirectUrl = $redirectUrl ? $redirectUrl : '/';

            return $this->redirect($redirectUrl);
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
     * @param string $direction Either 'upcoming' or 'past' (leave blank for 'upcoming')
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function location($locationSlug = null, $direction = 'upcoming')
    {
        if (!$locationSlug) {
            throw new BadRequestException('Error: No location name given.');
        }

        if (!in_array($direction, ['upcoming', 'past'])) {
            $direction = 'upcoming';
        }

        // For this page's results
        $primaryQuery = $this->Events
            ->find('published')
            ->find('withAllAssociated')
            ->find('ordered')
            ->find('atLocation', ['location_slug' => $locationSlug])
            ->find($direction);
        $count = $primaryQuery->count();
        $events = $this->paginate($primaryQuery)->toArray();

        // For finding the count of results in the other (past/upcoming) direction
        $secondaryQuery = $this->Events
            ->find('published')
            ->find('atLocation', ['location_slug' => $locationSlug])
            ->find(Application::oppositeDirection($direction));
        $countOtherDirection = $secondaryQuery->count();

        $locationName = $this->Events->getFullLocationName($locationSlug);
        if ($locationName == '') {
            $locationName = $locationSlug;
        }
        $pageTitle = $locationName == Event::VIRTUAL_LOCATION ? 'Virtual Events' : $locationName;

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
            ->find('published')
            ->find('past')
            ->select(['location', 'location_slug'])
            ->distinct(['location', 'location_slug'])
            ->orderAsc('location')
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

        $direction = $this->request->getQuery('direction') ?? 'upcoming';
        $counts = ['upcoming' => 0, 'past' => 0, 'all' => 0];

        // Get search results
        $query = $this->Events->getSearchResultsQuery($searchTerm, $direction);
        $counts[$direction] = $query->count();
        $order = $direction == 'past' ? 'DESC' : 'ASC';
        $query->epilog("ORDER BY Events__date $order, Events__time_start ASC");
        $events = $query->all()->toArray();

        if ($direction == 'all') {
            $timezone = Configure::read('localTimezone');
            $currentDate = (new FrozenTime('now', $timezone))->format('Y-m-d');
            foreach ($events as $event) {
                $key = $event->date->format('Y-m-d') >= $currentDate ? 'upcoming' : 'past';
                $counts[$key]++;
            }
        } else {
            // Determine if there are events in the opposite direction
            $otherDirection = Application::oppositeDirection($direction);
            $otherDirectionQuery = $this->Events->getSearchResultsQuery($searchTerm, $otherDirection);
            $counts[$otherDirection] = $otherDirectionQuery->count();
        }
        $this->set([
            'counts' => $counts,
            'direction' => $direction,
            'directionAdjective' => $direction,
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
        $userId = $this->Auth->user('id');
        if ($userId) {
            $query = $this->Events
                ->find('ordered', ['direction' => 'DESC'])
                ->where(['user_id' => $this->Auth->user('id')]);

            $events = $this->paginate($query)->toArray();
        } else {
            $events = [];
        }

        $this->set([
            'events' => $events,
            'pageTitle' => 'My Events',
        ]);
    }

    /**
     * Renders an iCal feed for all upcoming events (in the next year), optionally filtered by category
     *
     * /events/feed.ics, equivalent to /events/feed/all.ics
     * /events/feed/category-slug.ics (e.g. theater.ics)
     *
     * @param string $categorySlug
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function feed($categorySlug = 'all')
    {
        if (!$this->request->getParam('_ext') == 'ics') {
            throw new BadRequestException('.ics extension required');
        }

        // Get all published events for the next year
        $query = $this
            ->Events
            ->find('upcoming')
            ->find('published')
            ->find('withAllAssociated')
            ->find('ordered')
            ->where([
                function (QueryExpression $exp) {
                    return $exp->lte('date', (new FrozenTime('now + 1 year'))->format('Y-m-d'));
                },
            ]);

        // Limit events to a category
        if ($categorySlug != 'all') {
            $category = $this
                ->Events
                ->Categories
                ->find()
                ->where(['slug' => $categorySlug])
                ->first();
            if (!$category) {
                throw new BadRequestException('Invalid category: ' . $categorySlug);
            }
            $query->find('inCategory', ['categoryId' => $category->id]);
        }

        $events = $query->limit(1000)->all()->toArray();
        $filename = "$categorySlug.ics";
        $this->response = $this->response->withDownload($filename);
        $this->set(compact('events'));
        $this->response = $this->response->withType('text/calendar');
    }

    /**
     * A page that lists the available feeds that can be imported into Google Calendar
     *
     * @return void
     */
    public function feeds()
    {
        $categories = $this
            ->Events
            ->Categories
            ->find()
            ->orderAsc('weight')
            ->all();

        $this->set([
            'pageTitle' => 'Feeds',
            'categories' => $categories,
        ]);
    }

    /**
     * Shows the next or most recent official First Thursday post
     *
     * @return void
     */
    public function firstThursday(): void
    {
        $event = $this->Events->getNextOrLastFirstThursday();
        $isPast = $event ? ($event->date->isPast() && !$event->date->isToday()) : false;
        $pageTitle = 'First Thursday';
        $contactEmail = Configure::read('firstThursday.contactEmail');
        $this->set(compact('event', 'isPast', 'pageTitle', 'contactEmail'));
    }

    public function duplicate($eventId = null)
    {
        $eventExists = $this->Events->exists(['id' => $eventId]);
        if (!$eventExists) {
            throw new BadRequestException("Event with ID $eventId not found");
        }
        $event = $this->Events->get($eventId);

        $datesWithSameEventTitle = $this->Events
            ->find()
            ->where(['title' => $event->title])
            ->all()
            ->extract('date')
            ->toArray();

        if (!$this->userCanEdit($event)) {
            throw new ForbiddenException('You don\'t have permission to duplicate that event');
        }

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Auth->user();
            $copiedData = $event->toArray();
            unset($copiedData['id']);
            unset($copiedData['created']);
            unset($copiedData['modified']);

            $dates = explode('; ', $this->request->getData('date'));
            if ($dates) {
                sort($dates);
                $firstNewEventId = null;
                foreach ($dates as $date) {
                    $copiedData['date'] = new FrozenDate($date);
                    $newEvent = $this->Events->newEntity($copiedData);
                    $newEvent->autoApprove($user);
                    $newEvent->autoPublish($user);
                    if (!$this->Events->save($newEvent)) {
                        $this->Flash->error('Whuh oh. This event could not be duplicated. Details: ' . json_encode($newEvent->getErrors()));
                        break;
                    }
                    if (!$firstNewEventId) {
                        $firstNewEventId = $newEvent->id;
                    }
                }
                $this->Flash->success('Event duplicated to ' . count($dates) . ' date' . (count($dates) > 1 ? 's' : ''));
                return $this->redirect([
                    'controller' => 'Events',
                    'action' => 'view',
                    'id' => $firstNewEventId,
                ]);
            } else {
                $this->Flash->error('Please provide at least one date for the duplicated event.');
            }
        }

        $this->set([
            'datesWithSameEventTitle' => $datesWithSameEventTitle,
            'event' => $event,
            'pageTitle' => 'Duplicate event',
        ]);
    }
}
