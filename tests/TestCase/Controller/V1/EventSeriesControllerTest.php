<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;
use Cake\Utility\Hash;

/**
 * EventSeriesControllerTest class
 */
class EventSeriesControllerTest extends ApplicationTest
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
     * Tests that /event-series/{eventSeriesId} returns a successful response
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testViewSuccess()
    {
        $seriesId = 1;
        $this->get([
            'prefix' => 'v1',
            'controller' => 'EventSeries',
            'action' => 'view',
            $seriesId,
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseOk();

        $response = json_decode($this->_response->getBody());
        $this->assertNotEmpty($response->links->first);
        $this->assertNotEmpty($response->links->last);
        $this->assertNotEmpty($response->data);
        $seriesIds = Hash::extract($response->data, '{n}.relationships.series.data.id');
        $seriesIds = array_unique($seriesIds);
        $this->assertEquals([$seriesId], $seriesIds);
    }

    /**
     * Tests that /event-series/{eventSeriesId} fails for invalid methods
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testViewFailBadMethod()
    {
        $seriesId = 1;
        $url = [
            'prefix' => 'v1',
            'controller' => 'EventSeries',
            'action' => 'view',
            $seriesId,
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->assertDisallowedMethods($url, ['patch', 'put', 'post', 'delete']);
    }

    /**
     * Tests that /event-series/{eventSeriesId} fails for invalid series IDs
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testViewFailBadSeriesId()
    {
        $seriesId = 999;
        $this->get([
            'prefix' => 'v1',
            'controller' => 'EventSeries',
            'action' => 'view',
            $seriesId,
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseError();
    }
}
