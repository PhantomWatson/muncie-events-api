<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * EventSeriesControllerTest class
 */
class EventSeriesControllerTest extends ApplicationTest
{
    private $deleteUrl;

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

    public function setUp()
    {
        parent::setUp();

        $seriesId = 1;
        $userId = 1;
        $this->deleteUrl = [
            'prefix' => 'v1',
            'controller' => 'EventSeries',
            'action' => 'delete',
            $seriesId,
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken($userId)
            ]
        ];
    }

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

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} deletes an event returns a successful response
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testDeleteSuccess()
    {
        $seriesId = 1;

        $seriesTable = TableRegistry::getTableLocator()->get('EventSeries');
        $this->assertTrue($seriesTable->exists(['id' => $seriesId]));

        $this->delete($this->deleteUrl);
        $this->assertResponseCode(204);

        $this->assertFalse($seriesTable->exists(['id' => $seriesId]));
    }

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} fails for invalid methods
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testDeleteFailBadMethod()
    {
        $this->assertDisallowedMethods($this->deleteUrl, ['patch', 'put', 'post', 'get']);
    }

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} fails for invalid series IDs
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testDeleteFailBadSeriesId()
    {
        $seriesId = 999;
        $url = $this->deleteUrl;
        $url[0] = $seriesId;
        $this->delete($url);
        $this->assertResponseError();
    }
}
