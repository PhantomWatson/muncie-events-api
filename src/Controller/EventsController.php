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
            'tag',
            'view'
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
            'events' => $events
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
            'pageTitle' => $category->name
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
            'tag' => $tag
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
            'pageTitle' => $event->title
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
        $event = $this->Events->newEntityWithDefaults();

        $this->setEventFormVars($event);
        $this->set(['pageTitle' => 'Submit an Event',]);

        if (!$this->request->is(['patch', 'post', 'put'])) {
            return $this->render('form');
        }

        if (!$this->passedBotDetection()) {
            $this->Flash->error(
                'Spam detection failed. ' .
                'Please try the reCAPTCHA challenge again or log in before submitting an event.'
            );

            return $this->render('form');
        }

        // Add an event(s)
        $data = $this->request->getData() + [
                'images' => [],
                'tag_ids' => [],
                'tag_names' => [],
                'time_end' => null
            ];
        $dates = explode(',', $this->request->getData('date'));
        sort($dates);
        $eventForm = new EventForm();
        $user = $this->Auth->user();
        $addedEvents = [];
        try {
            foreach ($dates as $date) {
                $addedEvents[] = $eventForm->addSingleEvent($data, $date, $user);
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
     * @param Event $event
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
        $dateFieldValues = [];
        $preselectedDates = [];
        $defaultDate = 0; // Today
        $hasEndTime = (bool)$event->time_end;
        $hasAddress = (bool)$event->address;
        $hasCost = (bool)$event->cost;
        $hasAges = (bool)$event->age_restriction;
        $hasSource = (bool)$event->source;
        $hasMultipleDates = count($preselectedDates) > 1;
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $categories = $categoriesTable->find('list');
        $autocompleteLocations = [];
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        foreach ($eventsTable->getLocations() as $location) {
            $autocompleteLocations[] = [
                'label' => $location['location'],
                'value' => $location['address']
            ];
        }

        $this->set(compact(
            'action',
            'autocompleteLocations',
            'autoPublish',
            'categories',
            'dateFieldValues',
            'defaultDate',
            'event',
            'firstEvent',
            'hasAddress',
            'hasAges',
            'hasCost',
            'hasEndTime',
            'hasMultipleDates',
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
}
