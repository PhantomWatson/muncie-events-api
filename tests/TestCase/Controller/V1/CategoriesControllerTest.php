<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\Fixture\CategoriesFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\Utility\Hash;

/**
 * CategoriesControllerTest class
 */
class CategoriesControllerTest extends ApplicationTest
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
     * Tests that /v1/categories returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexSuccess()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Categories',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseOk();

        // Test that all categories are included
        $categories = (new CategoriesFixture())->records;
        $categoryIds = Hash::extract($categories, '{n}.id');
        sort($categoryIds);
        $response = (array)json_decode($this->_response->getBody());
        $responseCategories = $response['data'];
        $responseCategoryIds = Hash::extract($responseCategories, '{n}.id');
        sort($responseCategoryIds);
        $this->assertEquals(
            $categoryIds,
            $responseCategoryIds,
            sprintf(
                'Event categories expected: %s; Returned: %s',
                print_r($categoryIds, true),
                print_r($responseCategoryIds, true)
            )
        );

        // Test that event counts are non-zero
        $eventCounts = Hash::extract($responseCategories, '{n}.attributes.upcomingEventCount');
        $this->assertNotContains(
            0,
            $eventCounts,
            'One or more categories has a zero value for upcomingEventCount'
        );
    }
}
