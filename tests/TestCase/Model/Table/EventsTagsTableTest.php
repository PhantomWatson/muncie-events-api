<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsTagsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsTagsTable Test Case
 */
class EventsTagsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var EventsTagsTable
     */
    public $EventsTags;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.EventsTags',
        'app.Events',
        'app.Users',
        'app.Categories',
        'app.EventSeries',
        'app.Images',
        'app.EventsImages',
        'app.Tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('EventsTags') ? [] : ['className' => EventsTagsTable::class];
        $this->EventsTags = TableRegistry::get('EventsTags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsTags);

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
