<?php

namespace App\Test\TestCase\Controller\V1;

use App\Model\Entity\Event;
use App\Test\Fixture\CategoriesFixture;
use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\ImagesFixture;
use App\Test\Fixture\TagsFixture;
use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use PHPUnit\Exception;
use stdClass;

/**
 * EventsControllerTest class
 */
class EventsControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Categories',
        'app.EventSeries',
        'app.Events',
        'app.EventsImages',
        'app.EventsTags',
        'app.Images',
        'app.Tags',
        'app.Users'
    ];

    private $addingUserId = 1;
    private $addUrl;
    private $categoryUrl;
    private $deleteUrl;
    private $eventUrl;
    private $futureUrl;
    private $indexUrl;
    private $searchUrl;
    private $searchPastUrl;
    private $updateUrl;
    private $updateEventId = 1;
    private $eventStringFields = [
        'title',
        'description',
        'location',
        'location_details',
        'address',
        'age_restriction',
        'cost',
        'source'
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->addUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'add',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken($this->addingUserId)
            ]
        ];
        $this->updateUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'edit',
            $this->updateEventId,
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken()
            ]
        ];
        $this->deleteUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'delete',
            $this->updateEventId,
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken(1)
            ]
        ];
        $this->futureUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $event = (new EventsFixture())->records[0];
        $eventId = $event['id'];
        $this->eventUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'view',
            $eventId,
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->indexUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->searchUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'search',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->searchPastUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'search',
            'past',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $category = (new CategoriesFixture())->records[0];
        $categoryId = $category['id'];
        $this->categoryUrl = [
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'category',
            $categoryId,
            '?' => ['apikey' => $this->getApiKey()]
        ];
    }

    /**
     * Tests that /v1/events/future returns only future events
     *
     * @return void
     * @throws Exception
     */
    public function testFutureSuccess()
    {
        $this->get($this->futureUrl);
        $this->assertResponseOk();
        $this->assertResponseContains('event today');
        $this->assertResponseContains('event tomorrow');
        $this->assertResponseNotContains('event yesterday');
    }

    /**
     * Tests that /v1/events/future fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testFutureFailBadMethod()
    {
        $this->assertDisallowedMethods($this->futureUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /v1/events?start={date}&end={date} returns only events on the specified date
     *
     * @return void
     * @throws Exception
     */
    public function testIndexSuccessSpecificDate()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $url = $this->indexUrl;
        $url['?']['start'] = $date;
        $url['?']['end'] = $date;
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('event yesterday');
        $this->assertResponseNotContains('event today');
        $this->assertResponseNotContains('event tomorrow');
    }

    /**
     * Tests that /v1/events fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testIndexFailBadMethod()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $url = $this->indexUrl;
        $url['?']['start'] = $date;
        $url['?']['end'] = $date;
        $this->get($url);
        $this->assertDisallowedMethods($url, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that requests with invalid API keys are rejected
     *
     * @return void
     * @throws Exception
     */
    public function testFutureFailInvalidApiKey()
    {
        $url = $this->futureUrl;
        $url['?']['apikey'] = 'invalid key';
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests filtering in events by a single tag name
     *
     * @return void
     * @throws Exception
     */
    public function testFutureWithOneTagName()
    {
        $url = $this->futureUrl;
        $url['?']['withTags'] = [TagsFixture::TAG_NAME];
        $this->get($url);
        $this->assertResponseOk();
        $this->assertResponseContains('event with tag');
        $this->assertResponseNotContains('event without tag');
    }

    /**
     * Tests filtering in events by multiple tag names
     *
     * @return void
     * @throws Exception
     */
    public function testFutureWithMultipleTagNames()
    {
        $url = $this->futureUrl;
        $url['?']['withTags'] = [TagsFixture::TAG_NAME, TagsFixture::TAG_NAME_ALTERNATE];
        $this->get($url);

        $this->assertResponseOk();
        $this->assertResponseContains('event with tag');
        $this->assertResponseContains('event with different tag');
        $this->assertResponseNotContains('event without tag');
    }

    /**
     * Tests that an event is returned from /events/search if the search term is found in the event's title
     *
     * @return void
     * @throws Exception
     */
    public function testSearchInTitleSuccess()
    {
        $url = $this->searchUrl;
        $url['?']['q'] = EventsFixture::SEARCHABLE_TITLE;
        $this->get($url);
        $this->assertResponseOk();
        $eventIds = $this->getResponseEventIds();
        $this->assertContains(
            EventsFixture::EVENT_WITH_SEARCHABLE_TITLE,
            $eventIds,
            'Event with searchable title not in results'
        );
        $this->assertCount(
            1,
            $eventIds,
            'Results contain more than one event'
        );
    }

    /**
     * Tests that an event is returned from /events/search if the search term is found in the event's description
     *
     * @return void
     * @throws Exception
     */
    public function testSearchInDescriptionSuccess()
    {
        $url = $this->searchUrl;
        $url['?']['q'] = EventsFixture::SEARCHABLE_DESCRIPTION;
        $this->get($url);
        $this->assertResponseOk();
        $eventIds = $this->getResponseEventIds();
        $this->assertContains(
            EventsFixture::EVENT_WITH_SEARCHABLE_DESCRIPTION,
            $eventIds,
            'Event with searchable description not in results'
        );
        $this->assertCount(
            1,
            $eventIds,
            'Results contain more than one event'
        );
    }

    /**
     * Tests that an event is returned from /events/search if the search term is found in the event's location
     *
     * @return void
     * @throws Exception
     */
    public function testSearchInLocationSuccess()
    {
        $url = $this->searchUrl;
        $url['?']['q'] = EventsFixture::SEARCHABLE_LOCATION;
        $this->get($url);
        $this->assertResponseOk();
        $eventIds = $this->getResponseEventIds();
        $this->assertContains(
            EventsFixture::EVENT_WITH_SEARCHABLE_LOCATION,
            $eventIds,
            'Event with searchable location not in results'
        );
        $this->assertCount(
            1,
            $eventIds,
            'Results contain more than one event'
        );
    }

    /**
     * Tests that an error is thrown if the search term is empty
     *
     * @return void
     * @throws Exception
     */
    public function testSearchFailMissingParam()
    {
        $this->get($this->searchUrl);
        $this->assertResponseError();
        $response = json_decode($this->_response->getBody());
        $errorMsg = $response->errors[0]->detail;
        $this->assertEquals('The parameter "q" is required', $errorMsg);
    }

    /**
     * Tests that /v1/events/search (and .../past) fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testSearchFailBadMethod()
    {
        $this->assertDisallowedMethods($this->searchUrl, ['post', 'put', 'patch', 'delete']);
        $this->assertDisallowedMethods($this->searchPastUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that the correct events are returned from /events/category
     *
     * @return void
     * @throws Exception
     */
    public function testCategorySuccess()
    {
        $this->get($this->categoryUrl);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $responseCategoryIds = Hash::extract($response['data'], '{n}.relationships.category.data.id');
        $responseCategoryIds = array_unique($responseCategoryIds);
        $category = (new CategoriesFixture())->records[0];
        $categoryId = $category['id'];
        $this->assertEquals(
            [$categoryId],
            $responseCategoryIds,
            'Returned events were not limited to the specified category'
        );
    }

    /**
     * Tests that /v1/events/category fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testCategoryFailBadMethod()
    {
        $this->assertDisallowedMethods($this->categoryUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that the correct events are returned from /events/category?withTags[]=...
     *
     * @return void
     * @throws Exception
     */
    public function testCategoryWithTagSuccess()
    {
        $url = $this->categoryUrl;
        $url['?']['withTags'] = [TagsFixture::TAG_NAME];
        $this->get($url);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $responseCategoryIds = Hash::extract($response['data'], '{n}.relationships.category.data.id');
        $responseCategoryIds = array_unique($responseCategoryIds);
        $category = (new CategoriesFixture())->records[0];
        $categoryId = $category['id'];
        $this->assertEquals(
            [$categoryId],
            $responseCategoryIds,
            'Returned events were not limited to the specified category'
        );

        $responseTags = Hash::extract($response['data'], '{n}.attributes.tags.{n}.name');
        $this->assertContains(TagsFixture::TAG_NAME, $responseTags);
    }

    /**
     * Tests that the correct event is returned from /event/{eventID}
     *
     * @return void
     * @throws Exception
     */
    public function testEventSuccess()
    {
        $this->get($this->eventUrl);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $eventCount = count($response['data']);
        $this->assertEquals(1, $eventCount, "Expected one event to be returned, got $eventCount");

        $event = (new EventsFixture())->records[0];
        $eventId = $event['id'];
        $this->assertEquals($eventId, $response['data']->id, 'Unexpected event ID returned');
    }

    /**
     * Tests that /event fails with missing event ID
     *
     * @return void
     * @throws Exception
     */
    public function testEventFailIdMissing()
    {
        $url = $this->eventUrl;
        unset($url[0]);
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests that /event/{eventID} fails with invalid event ID
     *
     * @return void
     * @throws Exception
     */
    public function testEventFailInvalidId()
    {
        $url = $this->eventUrl;
        $url[0] = $this->getOutOfRangeId();
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests that all expected fields are included in events
     *
     * @throws Exception
     */
    public function testFutureEventFields()
    {
        $this->get($this->futureUrl);

        $response = json_decode($this->_response->getBody(), true);
        $event = $response['data'][0]['attributes'];
        $eventsFixture = new EventsFixture();
        $excludedFields = [
            'published',
            'approved_by',
            'created',
            'modified'
        ];
        $expectedFields = array_diff(array_keys($eventsFixture->fields), $excludedFields);
        foreach ($expectedFields as $field) {
            if ($field == 'id'
                || stripos($field, '_id') !== false
                || strpos($field, '_') === 0
            ) {
                continue;
            }
            $this->assertArrayHasKey($field, $event);
        }
    }

    /**
     * Returns an array of data to be passed along with a POST request to /event
     *
     * @return array
     */
    private function getAddSingleEventData()
    {
        $categoriesFixture = new CategoriesFixture();
        $category = $categoriesFixture->records[0];
        $imagesFixture = new ImagesFixture();

        return [
            'category_id' => $category['id'],
            'title' => 'Test Event Title',
            'description' => 'Test event description',
            'location' => 'Test location name',
            'location_details' => 'Test location details',
            'address' => 'Test address',
            'age_restriction' => '21+',
            'source' => 'Test info source',
            'cost' => 'Test cost',
            'date' => [
                date('Y-m-d', strtotime('tomorrow'))
            ],
            'time_start' => '02:00PM',
            'time_end' => '03:00PM',
            'tag_names' => [
                ' new tag 1 ',
                'NEW TAG 2'
            ],
            'tag_ids' => [
                TagsFixture::TAG_WITH_EVENT,
                TagsFixture::TAG_WITH_DIFFERENT_EVENT
            ],
            'images' => [
                [
                    'id' => $imagesFixture->records[1]['id'],
                    'caption' => 'Caption for first image'
                ],
                [
                    'id' => $imagesFixture->records[0]['id'],
                    'caption' => 'Caption for second image'
                ]
            ]
        ];
    }

    /**
     * Tests that a POST request to /event with full data for a single event succeeds
     *
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testAddSingleEventSuccess()
    {
        $data = $this->getAddSingleEventData();
        $this->post($this->addUrl, $data);
        $this->assertResponseOk();

        // Check misc. attributes
        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;
        foreach ($this->eventStringFields as $field) {
            $this->assertEquals($data[$field], $returnedEvent->$field);
        }
        $this->assertEquals($data['date'][0], $returnedEvent->date);
        $this->assertEmpty($returnedEvent->series);
        $this->checkUrl($response);
        $date = new FrozenDate($data['date'][0]);
        $this->checkTimes($date, $data, $returnedEvent);
        $this->assertTrue(
            $returnedEvent->published,
            'Event not auto-published for user with previous published events'
        );

        // Check user
        $usersFixture = new UsersFixture();
        $user = $usersFixture->records[$this->addingUserId - 1];
        $this->assertEquals($user['name'], $returnedEvent->user->name);
        $this->assertEquals($user['email'], $returnedEvent->user->email);

        // Check category
        $categoriesFixture = new CategoriesFixture();
        $expectedCategoryName = $categoriesFixture->records[0]['name'];
        $this->checkCategory($expectedCategoryName, $returnedEvent);

        // Check tag names
        $expectedTagNames = $this->cleanTagNames($data['tag_names']);
        $expectedTagNames[] = TagsFixture::TAG_NAME;
        $expectedTagNames[] = TagsFixture::TAG_NAME_ALTERNATE;
        $this->checkTagNames($expectedTagNames, $returnedEvent);

        $this->checkImages($data, $returnedEvent);

        // Check relationships
        $relationships = $response->data->relationships;
        $this->assertEquals($relationships->category->data->id, $data['category_id']);
        $this->assertEmpty($relationships->series->data);
        $returnedTagIds = Hash::extract($relationships->tags->data, '{n}.id');
        $this->assertContains(TagsFixture::TAG_WITH_EVENT, $returnedTagIds);
        $this->assertContains(TagsFixture::TAG_WITH_DIFFERENT_EVENT, $returnedTagIds);
        $this->assertEquals($relationships->user->data->id, $this->addingUserId);
    }

    /**
     * Tests that /event fails for non-post requests
     *
     * @return void
     * @throws Exception
     */
    public function testAddFailBadMethod()
    {
        $this->assertDisallowedMethods($this->addUrl, ['get', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that POST /event fails for invalid category IDs
     *
     * @return void
     * @throws Exception
     */
    public function testAddFailInvalidCategoryId()
    {
        $invalidId = 999;
        $data = $this->getAddSingleEventData();
        $data['category_id'] = $invalidId;
        $this->post($this->addUrl, $data);
        $this->assertResponseError();
    }

    /**
     * Tests that POST /event fails for invalid image IDs
     *
     * @return void
     * @throws Exception
     */
    public function testAddFailInvalidImageId()
    {
        $invalidId = 999;
        $data = $this->getAddSingleEventData();
        $data['images'] = [['id' => $invalidId]];
        $this->post($this->addUrl, $data);
        $this->assertResponseError();
    }

    /**
     * Tests that POST /event fails for invalid tag IDs
     *
     * @return void
     * @throws Exception
     */
    public function testAddFailInvalidTagId()
    {
        $invalidId = 999;
        $data = $this->getAddSingleEventData();
        $data['tag_ids'] = [$invalidId];
        $this->post($this->addUrl, $data);
        $this->assertResponseError();
    }

    /**
     * Tests that POST /event fails for missing or blank required data
     *
     * @return void
     * @throws Exception
     */
    public function testAddFailMissingRequiredData()
    {
        $requiredFields = [
            'category_id',
            'title',
            'description',
            'location',
            'date',
            'time_start'
        ];
        $validData = $this->getAddSingleEventData();
        foreach ($requiredFields as $requiredField) {
            $invalidData = $validData;

            $invalidData[$requiredField] = null;
            $this->post($this->addUrl, $invalidData);
            $this->assertResponseError("Error not triggered for blank $requiredField");

            unset($invalidData[$requiredField]);
            $this->post($this->addUrl, $invalidData);
            $this->assertResponseError("Error not triggered for missing $requiredField");
        }

        $invalidData = $validData;
        $invalidData['date'] = [];
        $this->post($this->addUrl, $invalidData);
        $this->assertResponseError("Error not triggered for empty date array");
    }

    /**
     * Tests that POST /event still succeeds when optional data is blank or missing
     *
     * @return void
     * @throws Exception
     */
    public function testAddSuccessMissingOptionalData()
    {
        $optionalFields = [
            'time_end' => '',
            'location_details' => '',
            'address' => '',
            'cost' => '',
            'age_restriction' => '',
            'source' => '',
            'images' => [],
            'tag_ids' => [],
            'tag_names' => []
        ];
        $validData = $this->getAddSingleEventData();
        foreach ($optionalFields as $optionalField => $blankValue) {
            $partialData = $validData;

            $partialData[$optionalField] = $blankValue;
            $this->post($this->addUrl, $partialData);
            $this->assertResponseOk("Error triggered for blank optional field $optionalField");

            unset($partialData[$optionalField]);
            $this->post($this->addUrl, $partialData);
            $this->assertResponseOk("Error triggered for missing blank optional field $optionalField");
        }
    }

    /**
     * Tests that POST /event still succeeds when using alternate time formats listed in the API docs
     *
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testAddSuccessAlternateTimeFormats()
    {
        $data = $this->getAddSingleEventData();
        $times = [
            '2:30pm' => '3:30pm',
            '02:30pm' => '03:30pm',
            '2:30PM' => '3:30PM',
            '02:30PM' => '03:30PM',
            '2:30 pm' => '3:30 pm',
            '02:30 pm' => '03:30 pm',
            '2:30 PM' => '3:30 PM',
            '02:30 PM' => '03:30 PM',
            '14:30' => '15:30',
            '2:30' => '3:30' // AM
        ];
        $date = new FrozenDate($data['date'][0]);

        foreach ($times as $startTime => $endTime) {
            $data['time_start'] = $startTime;
            $data['time_end'] = $endTime;
            $this->post($this->addUrl, $data);
            $this->assertResponseOk("Error triggered for times $startTime to $endTime");

            $response = json_decode($this->_response->getBody());
            $event = $response->data->attributes;

            $meridian = $startTime == '2:30' ? 'am' : 'pm';
            foreach (['start', 'end'] as $whichTime) {
                $time = $whichTime == 'start' ? "2:30$meridian" : "3:30$meridian";
                $expected = Event::getDatetime($date, new FrozenTime($time));
                $actual = $event->{"time_$whichTime"};
                $this->assertEquals($expected, $actual, "Expected $whichTime time $expected was actually $actual");
            }
        }
    }

    /**
     * Tests that POST /event still succeeds when using alternate time formats listed in the API docs
     *
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testAddSuccessAnonUser()
    {
        $url = $this->addUrl;
        unset($url['?']['userToken']);
        $data = $this->getAddSingleEventData();
        $this->post($url, $data);
        $this->assertResponseOk("Error triggered when posting event anonymously");
    }

    /**
     * Tests that POST /event still succeeds when passing tag_names as a comma-delimited string
     *
     * @return void
     * @throws Exception
     */
    public function testAddSuccessCommaDelimitedTags()
    {
        $data = $this->getAddSingleEventData();
        $expectedTagNames = $this->cleanTagNames($data['tag_names']);

        $data['tag_names'] = implode(', ', $data['tag_names']);
        $this->post($this->addUrl, $data);
        $this->assertResponseOk("Error triggered when posting event with comma-delimited tag names");

        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;
        $actualTagNames = Hash::extract($returnedEvent->tags, '{n}.name');
        foreach ($expectedTagNames as $expectedTagName) {
            $this->assertContains($expectedTagName, $actualTagNames);
        }
    }

    /**
     * Tests that POST /event succeeds when passing multiple dates
     *
     * @return void
     * @throws Exception
     */
    public function testAddSuccessMultipleDates()
    {
        $data = $this->getAddSingleEventData();
        $data['date'][] = date('Y-m-d', strtotime($data['date'][0] . ' + 1 day'));
        $this->post($this->addUrl, $data);
        $this->assertResponseOk("Error triggered when posting event with multiple days");

        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;

        // Check that series info is returned
        $this->assertNotEmpty($returnedEvent->series);
        $this->assertEquals($data['title'], $returnedEvent->series->title);
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $seriesId = $response->data->relationships->series->data->id;
        $this->assertEquals(
            $baseUrl . '/event_series/' . $seriesId,
            $returnedEvent->series->url
        );

        // Check added events
        $expectedEventsCount = count($data['date']);
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $events = $eventsTable->find()
            ->where(['series_id' => $seriesId])
            ->all();
        $actualEventsCount = $events->count();
        $this->assertEquals($expectedEventsCount, $actualEventsCount);
        $savedDates = [];
        foreach ($events as $event) {
            $savedDates[] = $event->date->format('Y-m-d');
        }
        foreach ($data['date'] as $date) {
            $this->assertContains($date, $savedDates, "Expected event with date $date not saved to database");
        }
    }

    /**
     * Tests that POST /event does NOT auto-publish events for non-qualifying users
     *
     * @return void
     * @throws Exception
     */
    public function testAddNotAutoPublished()
    {
        $usersFixture = new UsersFixture();
        $userTokens = Hash::combine($usersFixture->records, '{n}.id', '{n}.token');
        $userTokenWithoutEvents = $userTokens[UsersFixture::USER_WITHOUT_EVENTS];

        $url = $this->addUrl;
        $url['?']['userToken'] = $userTokenWithoutEvents;
        $data = $this->getAddSingleEventData();
        $this->post($url, $data);
        $this->assertResponseOk();

        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;
        $this->assertFalse(
            $returnedEvent->published,
            'Event auto-published for non-qualifying user'
        );

        unset($url['?']['userToken']);
        $this->post($url, $data);
        $this->assertResponseOk();

        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;
        $this->assertFalse(
            $returnedEvent->published,
            'Event auto-published for non-qualifying user'
        );
    }

    /**
     * Tests that a PATCH request to /event/{eventId} with full, valid data succeeds
     *
     * @return void
     * @throws Exception
     * @throws \Exception
     */
    public function testUpdateFullEventSuccess()
    {
        // Compose update data
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $event */
        $event = $eventsTable->get($this->updateEventId);
        $data = [
            'tag_ids' => [TagsFixture::TAG_WITH_EVENT],
            'tag_names' => [TagsFixture::TAG_NAME_ALTERNATE],
            'time_start' => (new Time($event->time_start))->addHour(1)->format('h:ia'),
            'time_end' => (new Time($event->time_end))->addHour(1)->format('h:ia'),
            'date' => $event->date->addDay(1)->format('Y-m-d'),
            'category_id' => $event->category_id + 1,
            'images' => [[
                'id' => 2,
                'caption' => 'Updated caption'
            ]]
        ];
        foreach ($this->eventStringFields as $field) {
            $data[$field] = $event->$field . ' updated';
        }

        // Send update request
        $this->patch($this->updateUrl, $data);
        $this->assertResponseOk();

        // Check misc. attributes
        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;
        foreach ($this->eventStringFields as $field) {
            $this->assertEquals($data[$field], $returnedEvent->$field);
        }
        $this->assertEquals($data['date'], $returnedEvent->date);
        $this->checkUrl($response);
        $date = new FrozenDate($data['date']);
        $this->checkTimes($date, $data, $returnedEvent);
        $this->assertTrue(
            $returnedEvent->published,
            'Event not auto-published for user with previous published events'
        );

        // Check user
        $usersFixture = new UsersFixture();
        $user = $usersFixture->records[$this->addingUserId - 1];
        $this->assertEquals($user['name'], $returnedEvent->user->name);
        $this->assertEquals($user['email'], $returnedEvent->user->email);

        // Check category
        $categoriesFixture = new CategoriesFixture();
        $categoryNames = Hash::combine($categoriesFixture->records, '{n}.id', '{n}.name');
        $expectedCategoryName = $categoryNames[$data['category_id']];
        $this->checkCategory($expectedCategoryName, $returnedEvent);

        // Check tag names
        $expectedTagNames = $this->cleanTagNames($data['tag_names']);
        $expectedTagNames[] = TagsFixture::TAG_NAME;
        $this->checkTagNames($expectedTagNames, $returnedEvent);

        $this->checkImages($data, $returnedEvent);

        // Check relationships
        $relationships = $response->data->relationships;
        $this->assertEquals($relationships->category->data->id, $data['category_id']);
        $returnedTagIds = Hash::extract($relationships->tags->data, '{n}.id');
        $this->assertContains(TagsFixture::TAG_WITH_EVENT, $returnedTagIds);
        $this->assertContains(TagsFixture::TAG_WITH_DIFFERENT_EVENT, $returnedTagIds);
        $this->assertEquals($relationships->user->data->id, $this->addingUserId);
    }

    /**
     * Performs assertions on a returned event's image data
     *
     * @param array $data Data included in request
     * @param Event $returnedEvent Returned event entity
     * @return void
     */
    private function checkImages(array $data, $returnedEvent)
    {
        $imagesFixture = new ImagesFixture();
        $filenames = Hash::combine($imagesFixture->records, '{n}.id', '{n}.filename');
        $count = count($data['images']);
        for ($n = 0; $n < $count; $n++) {
            $this->assertEquals($data['images'][$n]['caption'], $returnedEvent->images[$n]->caption);
            foreach (['full', 'small', 'tiny'] as $size) {
                $imageId = $data['images'][$n]['id'];
                $filename = $filenames[$imageId];
                $imgUrl = $returnedEvent->images[$n]->{$size . '_url'};
                $this->assertStringEndsWith("$size/$filename", $imgUrl);
            }
        }
    }

    /**
     * Performs assertions on a returned event's category data
     *
     * @param string $expectedCategoryName Expected category name
     * @param stdClass $returnedEvent Event entity
     * @return void
     */
    private function checkCategory($expectedCategoryName, $returnedEvent)
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $this->assertEquals($expectedCategoryName, $returnedEvent->category->name);
        $this->assertNotEmpty($returnedEvent->category->url);
        $this->assertStringStartsWith('https://', $returnedEvent->category->url);
        $this->assertStringStartsWith($baseUrl, $returnedEvent->category->url);
        $this->assertStringStartsWith('https://', $returnedEvent->category->icon->svg);
    }

    /**
     * Trims and strtolower()s an array of tag names
     *
     * @param array $tagNames Array of tag names
     * @return array
     */
    private function cleanTagNames(array $tagNames)
    {
        $tagNames = array_map('strtolower', $tagNames);
        $tagNames = array_map('trim', $tagNames);

        return $tagNames;
    }

    /**
     * Performs assertions on a returned event's tags
     *
     * @param array $expectedTagNames Array of expected tag names
     * @param Event $returnedEvent Returned event entity
     * @return void
     */
    private function checkTagNames(array $expectedTagNames, $returnedEvent)
    {
        $actualTagNames = Hash::extract($returnedEvent->tags, '{n}.name');
        sort($actualTagNames);
        sort($expectedTagNames);
        $this->assertEquals($expectedTagNames, $actualTagNames);
    }

    /**
     * Performs an assertion on a returned event's URL
     *
     * @param stdClass $response Response object
     * @return void
     */
    private function checkUrl($response)
    {
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $this->assertEquals($baseUrl . '/event/' . $response->data->id, $response->data->attributes->url);
    }

    /**
     * Performs assertions on a returned event's time fields
     *
     * @param FrozenDate $date Date object
     * @param array $data Data sent in request
     * @param Event $returnedEvent Event entity
     * @throws \Exception
     * @return void
     */
    private function checkTimes($date, $data, $returnedEvent)
    {
        foreach (['start', 'end'] as $whichTime) {
            $expected = Event::getDatetime($date, new FrozenTime($data["time_$whichTime"]));
            $actual = $returnedEvent->{"time_$whichTime"};
            $this->assertEquals($expected, $actual, "Expected $whichTime time $expected was actually $actual");
        }
    }

    /**
     * Tests that PATCH /event/{eventId} fails for non-patch requests
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateFailBadMethod()
    {
        $this->assertDisallowedMethods($this->updateUrl, ['get', 'put', 'post', 'delete']);
    }

    /**
     * Tests that PATCH /event/{eventId} fails for invalid category IDs
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateFailInvalidEventId()
    {
        $url = $this->updateUrl;
        $invalidEventId = 9999;
        $url[0] = $invalidEventId;
        $this->patch($url, []);
        $this->assertResponseError();
        $response = json_decode($this->_response->getBody());
        $this->assertContains('not found', $response->errors[0]->detail);
    }

    /**
     * Tests that PATCH /event/{eventId} fails for missing user token
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateFailMissingUserToken()
    {
        $url = $this->updateUrl;
        unset($url['?']['userToken']);
        $this->patch($url, []);
        $this->assertResponseError();
        $response = json_decode($this->_response->getBody());
        $this->assertContains('don\'t have permission', $response->errors[0]->detail);
    }

    /**
     * Tests that PATCH /event/{eventId} fails for an invalid user token
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateFailInvalidUserToken()
    {
        $url = $this->updateUrl;
        $url['?']['userToken'] .= 'invalid';
        $this->patch($url, []);
        $this->assertResponseError();
        $response = json_decode($this->_response->getBody());
        $this->assertContains('token invalid', $response->errors[0]->detail);
    }

    /**
     * Tests that PATCH /event/{eventId} fails for an unauthorized user token
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateFailUnauthUserToken()
    {
        $userId = 2;
        $url = $this->updateUrl;
        $usersFixture = new UsersFixture();
        $userTokens = Hash::combine($usersFixture->records, '{n}.id', '{n}.token');
        $url['?']['userToken'] = $userTokens[$userId];
        $this->patch($url, ['title' => 'new title']);
        $this->assertResponseError();
        $response = json_decode($this->_response->getBody());
        $this->assertContains('don\'t have permission', $response->errors[0]->detail);
    }

    /**
     * Tests that PATCH /event/{eventId} can not update any protected fields
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateCantUpdateProtectedFields()
    {
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $eventBefore */
        $eventBefore = $eventsTable->get($this->updateEventId);

        foreach ($eventBefore->updateProtectedFields as $protectedField) {
            $data = [$protectedField => 'updated'];
            $this->patch($this->updateUrl, $data);
            $this->assertResponseError("Error not thrown when attempting to update $protectedField");

            $eventAfter = $eventsTable->get($this->updateEventId);
            $this->assertEquals($eventBefore, $eventAfter);

            $response = json_decode($this->_response->getBody());
            $this->assertContains('field is not allowed', $response->errors[0]->detail);
        }
    }

    /**
     * Tests that an event can be successfully deleted
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteSuccess()
    {
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $count = $eventsTable->find()->where(['id' => $this->updateEventId])->count();
        $this->assertEquals(1, $count, 'Event targeted for delete does not exist');

        $this->delete($this->deleteUrl);
        $this->assertResponseCode(204);

        $count = $eventsTable->find()->where(['id' => $this->updateEventId])->count();
        $this->assertEquals(0, $count, 'Event was not deleted');
    }

    /**
     * Tests that the method invoked for DELETE /event/{eventId} fails for non-delete requests
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteFailBadMethod()
    {
        $this->assertDisallowedMethods($this->deleteUrl, ['get', 'put', 'post', 'patch']);
    }

    /**
     * Tests that an event cannot be deleted if the provided user is not its owner
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteFailNotOwner()
    {
        $url = $this->deleteUrl;
        $url['?']['userToken'] = $this->getUserToken(2);
        $this->delete($url);
        $this->assertResponseError();
    }

    /**
     * Tests that DELETE /event fails with missing event ID
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteFailIdMissing()
    {
        $url = $this->deleteUrl;
        unset($url[0]);
        $this->delete($url);
        $this->assertResponseError();
    }

    /**
     * Tests that DELETE /event/{eventID} fails with invalid event ID
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteFailInvalidId()
    {
        $url = $this->deleteUrl;
        $url[0] = $this->getOutOfRangeId();
        $this->delete($url);
        $this->assertResponseError();
    }

    /**
     * Returns an integer that is outside of the range of current event IDs
     *
     * @return int
     */
    private function getOutOfRangeId()
    {
        $events = (new EventsFixture())->records;
        $eventIds = Hash::extract($events, '{n}.id');
        sort($eventIds);

        return array_pop($eventIds) + 1;
    }
}
