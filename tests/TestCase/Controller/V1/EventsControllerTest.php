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
use Cake\Utility\Hash;

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
    }

    /**
     * Tests that /v1/events/future returns only future events
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testFuture()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseContains('event today');
        $this->assertResponseContains('event tomorrow');
        $this->assertResponseNotContains('event yesterday');
    }

    /**
     * Tests that /v1/events?start={date}&end={date} returns only events on the specified date
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testSpecificDate()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'index',
            '?' => [
                'apikey' => $this->getApiKey(),
                'start' => $date,
                'end' => $date
            ]
        ]);
        $this->assertResponseContains('event yesterday');
        $this->assertResponseNotContains('event today');
        $this->assertResponseNotContains('event tomorrow');
    }

    /**
     * Tests that requests with invalid API keys are rejected
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testInvalidApiKey()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => ['apikey' => 'invalid key']
        ]);
        $this->assertResponseError();
    }

    /**
     * Tests filtering in events by a single tag name
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testWithOneTagName()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => [
                'apikey' => $this->getApiKey(),
                'withTags' => [TagsFixture::TAG_NAME]
            ]
        ]);
        $this->assertResponseContains('event with tag');
        $this->assertResponseNotContains('event without tag');
    }

    /**
     * Tests filtering in events by multiple tag names
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testWithMultipleTagNames()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => [
                'apikey' => $this->getApiKey(),
                'withTags' => [TagsFixture::TAG_NAME, TagsFixture::TAG_NAME_ALTERNATE]
            ]
        ]);
        $this->assertResponseContains('event with tag');
        $this->assertResponseContains('event with different tag');
        $this->assertResponseNotContains('event without tag');
    }

    /**
     * Tests that an event is returned from /events/search if the search term is found in the event's title
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testSearchInTitleSuccess()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'search',
            '?' => [
                'apikey' => $this->getApiKey(),
                'q' => EventsFixture::SEARCHABLE_TITLE
            ]
        ]);
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
     * @throws \PHPUnit\Exception
     */
    public function testSearchInDescriptionSuccess()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'search',
            '?' => [
                'apikey' => $this->getApiKey(),
                'q' => EventsFixture::SEARCHABLE_DESCRIPTION
            ]
        ]);
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
     * @throws \PHPUnit\Exception
     */
    public function testSearchInLocationSuccess()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'search',
            '?' => [
                'apikey' => $this->getApiKey(),
                'q' => EventsFixture::SEARCHABLE_LOCATION
            ]
        ]);
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
     * @throws \PHPUnit\Exception
     */
    public function testSearchFailMissingParam()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'search',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseError();
        $response = json_decode($this->_response->getBody());
        $errorMsg = $response->errors[0]->detail;
        $this->assertEquals('The parameter "q" is required', $errorMsg);
    }

    /**
     * Tests that the correct events are returned from /events/category
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testCategorySuccess()
    {
        $category = (new CategoriesFixture())->records[0];
        $categoryId = $category['id'];
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'category',
            $categoryId,
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $responseCategoryIds = Hash::extract($response['data'], '{n}.relationships.category.data.id');
        $responseCategoryIds = array_unique($responseCategoryIds);
        $this->assertEquals(
            [$categoryId],
            $responseCategoryIds,
            'Returned events were not limited to the specified category'
        );
    }

    /**
     * Tests that the correct events are returned from /events/category?withTags[]=...
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testCategoryWithTagSuccess()
    {
        $category = (new CategoriesFixture())->records[0];
        $categoryId = $category['id'];
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'category',
            $categoryId,
            '?' => [
                'apikey' => $this->getApiKey(),
                'withTags' => [TagsFixture::TAG_NAME]
            ]
        ]);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $responseCategoryIds = Hash::extract($response['data'], '{n}.relationships.category.data.id');
        $responseCategoryIds = array_unique($responseCategoryIds);
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
     * @throws \PHPUnit\Exception
     */
    public function testEventSuccess()
    {
        $event = (new EventsFixture())->records[0];
        $eventId = $event['id'];
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'view',
            $eventId,
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $eventCount = count($response['data']);
        $this->assertEquals(1, $eventCount, "Expected one event to be returned, got $eventCount");

        $this->assertEquals($eventId, $response['data']->id, 'Unexpected event ID returned');
    }

    /**
     * Tests that /event fails with missing event ID
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testEventFailIdMissing()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'view',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseError();
    }

    /**
     * Tests that /event/{eventID} fails with invalid event ID
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testEventFailInvalidId()
    {
        $events = (new EventsFixture())->records;
        $eventIds = Hash::extract($events, '{n}.id');
        sort($eventIds);
        $outOfRangeId = array_pop($eventIds) + 1;

        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'view',
            $outOfRangeId,
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseError();
    }

    /**
     * Tests that all expected fields are included in events
     *
     * @throws \PHPUnit\Exception
     */
    public function testFutureEventFields()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => ['apikey' => $this->getApiKey()]
        ]);

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
     * @throws \PHPUnit\Exception
     */
    public function testAddSingleEventSuccess()
    {
        $data = $this->getAddSingleEventData();
        $this->post($this->addUrl, $data);
        $this->assertResponseOk();

        // Check misc. attributes
        $response = json_decode($this->_response->getBody());
        $returnedEvent = $response->data->attributes;
        $fields = [
            'title',
            'description',
            'location',
            'location_details',
            'address',
            'age_restriction',
            'source',
            'cost',
        ];
        foreach ($fields as $field) {
            $this->assertEquals($data[$field], $returnedEvent->$field);
        }
        $this->assertEquals($data['date'][0], $returnedEvent->date);
        $this->assertEmpty($returnedEvent->series);
        $baseUrl = Configure::read('mainSiteBaseUrl');
        $this->assertEquals($baseUrl . '/event/' . $response->data->id, $returnedEvent->url);

        // Check user
        $usersFixture = new UsersFixture();
        $user = $usersFixture->records[$this->addingUserId - 1];
        $this->assertEquals($user['name'], $returnedEvent->user->name);
        $this->assertEquals($user['email'], $returnedEvent->user->email);

        // Check category
        $categoriesFixture = new CategoriesFixture();
        $category = $categoriesFixture->records[0];
        $this->assertEquals($category['name'], $returnedEvent->category->name);
        $this->assertNotEmpty($returnedEvent->category->url);
        $this->assertStringStartsWith('https://', $returnedEvent->category->url);
        $this->assertStringStartsWith($baseUrl, $returnedEvent->category->url);
        $this->assertStringStartsWith('https://', $returnedEvent->category->icon->svg);

        // Check tag names
        $expectedTagNames = $data['tag_names'];
        $expectedTagNames = array_map('strtolower', $expectedTagNames);
        $expectedTagNames = array_map('trim', $expectedTagNames);
        $expectedTagNames[] = TagsFixture::TAG_NAME;
        $expectedTagNames[] = TagsFixture::TAG_NAME_ALTERNATE;
        $actualTagNames = Hash::extract($returnedEvent->tags, '{n}.name');
        sort($actualTagNames);
        sort($expectedTagNames);
        $this->assertEquals($expectedTagNames, $actualTagNames);

        // Check images
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
     * @throws \PHPUnit\Exception
     */
    public function testAddFailBadMethod()
    {
        $this->assertDisallowedMethods($this->addUrl, ['get', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that POST /event fails for invalid category IDs
     *
     * @return void
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
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
                $expected = Event::getCorrectedTime($date, new FrozenTime($time));
                $actual = $event->{"time_$whichTime"};
                $this->assertEquals($expected, $actual, "Expected $whichTime time $expected was actually $actual");
            }
        }
    }
}
