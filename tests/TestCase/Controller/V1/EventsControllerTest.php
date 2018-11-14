<?php
namespace App\Test\TestCase\Controller;

use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\UsersFixture;
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
        'app.api_calls',
        'app.categories',
        'app.event_series',
        'app.events',
        'app.events_images',
        'app.events_tags',
        'app.images',
        'app.tags',
        'app.users'
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => [
                'HTTPS' => 'on'
            ]
        ]);
    }

    /**
     * Returns a simple array of the IDs of all events returned in the JSON response to the last request
     *
     * @return array|\ArrayAccess
     */
    private function getResponseEventIds()
    {
        $response = (array)json_decode($this->_response->getBody());

        return Hash::extract($response['data'], '{n}.id');
    }

    /**
     * Returns a valid API key
     *
     * @return mixed
     */
    private function getApiKey()
    {
        $usersFixture = new UsersFixture();

        return $usersFixture->records[0]['api_key'];
    }

    /**
     * Tests that /v1/events/future returns only future events
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testFuture()
    {
        $usersFixture = new UsersFixture();
        $apiKey = $usersFixture->records[0]['api_key'];
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => ['apikey' => $apiKey]
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
        $usersFixture = new UsersFixture();
        $apiKey = $usersFixture->records[0]['api_key'];
        $date = date('Y-m-d', strtotime('yesterday'));
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'index',
            '?' => [
                'apikey' => $apiKey,
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
        $usersFixture = new UsersFixture();
        $apiKey = $usersFixture->records[0]['api_key'];
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => [
                'apikey' => $apiKey,
                'withTags' => ['test tag']
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
        $usersFixture = new UsersFixture();
        $apiKey = $usersFixture->records[0]['api_key'];
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Events',
            'action' => 'future',
            '?' => [
                'apikey' => $apiKey,
                'withTags' => ['test tag', 'another tag']
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
            '?' => [
                'apikey' => $this->getApiKey()
            ]
        ]);
        $this->assertResponseError();
        $this->assertResponseContains('The parameter \"q\" is required');
    }
}
