<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\Fixture\CategoriesFixture;
use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\TagsFixture;
use App\Test\TestCase\ApplicationTest;
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

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
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
        $this->assertResponseContains('The parameter \"q\" is required');
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
        $responseCategoryIds = Hash::extract($response['data'], '{n}.attributes.category.id');
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
        $responseCategoryIds = Hash::extract($response['data'], '{n}.attributes.category.id');
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
}
