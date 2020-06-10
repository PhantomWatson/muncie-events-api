<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use PHPUnit\Exception;

/**
 * EventSeriesControllerTest class
 */
class EventSeriesControllerTest extends ApplicationTest
{
    private $deleteUrl;
    private $viewUrl;

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
        'app.Users',
    ];

    /**
     * Sets up this series of tests
     *
     * @return void
     */
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
                'userToken' => $this->getUserToken($userId),
            ],
        ];

        $seriesId = 1;
        $this->viewUrl = [
            'prefix' => 'v1',
            'controller' => 'EventSeries',
            'action' => 'view',
            $seriesId,
        ];
    }

    /**
     * Tests that GET /v1/event-series/{eventSeriesId} returns a successful response
     *
     * @return void
     * @throws Exception
     */
    public function testViewSuccess()
    {
        $seriesId = 1;
        $this->get($this->viewUrl);
        $this->assertResponseOk();

        $response = json_decode($this->_response->getBody());
        $this->assertNotEmpty($response->links->first);
        $this->assertNotEmpty($response->links->last);
        $this->assertNotEmpty($response->data);
        $returnedSeriesIds = Hash::extract($response->data, '{n}.relationships.series.data.id');
        $returnedSeriesIds = array_unique($returnedSeriesIds);
        $this->assertEquals([$seriesId], $returnedSeriesIds);
    }

    /**
     * Tests that GET /v1/event-series/{eventSeriesId} fails for invalid methods
     *
     * @return void
     * @throws Exception
     */
    public function testViewFailBadMethod()
    {
        $this->assertDisallowedMethods($this->viewUrl, ['patch', 'put', 'post', 'delete']);
    }

    /**
     * Tests that GET /v1/event-series/{eventSeriesId} fails for invalid series IDs
     *
     * @return void
     * @throws Exception
     */
    public function testViewFailBadSeriesId()
    {
        $seriesId = 999;
        $url = $this->viewUrl;
        $url[0] = $seriesId;
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} deletes an event returns a successful response
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function testDeleteFailBadMethod()
    {
        $this->assertDisallowedMethods($this->deleteUrl, ['patch', 'put', 'post', 'get']);
    }

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} fails for invalid series IDs
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteFailBadSeriesId()
    {
        $seriesId = 999;
        $url = $this->deleteUrl;
        $url[0] = $seriesId;
        $this->delete($url);
        $this->assertResponseError();
    }

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} fails for missing user tokens
     *
     * @return void
     * @throws Exception
     */
    public function testDeleteFailNoToken()
    {
        $url = $this->deleteUrl;
        unset($url['?']['userToken']);
        $this->delete($url);
        $this->assertResponseError();
    }

    /**
     * Tests that DELETE /v1/event-series/{eventSeriesId} fails for non-owners
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
}
