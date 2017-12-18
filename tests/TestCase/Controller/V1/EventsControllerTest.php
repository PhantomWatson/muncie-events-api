<?php
namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;

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
     * Tests that /v1/events/future returns only future events
     *
     * @return void
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
}
