<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsImagesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsImagesTable Test Case
 */
class EventsImagesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var EventsImagesTable
     */
    public $EventsImages;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.EventsImages',
        'app.Images',
        'app.Events',
        'app.Users',
        'app.Categories',
        'app.EventSeries',
        'app.Tags',
        'app.EventsTags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('EventsImages') ? [] : ['className' => EventsImagesTable::class];
        $this->EventsImages = TableRegistry::getTableLocator()->get('EventsImages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsImages);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
