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
     * @var \App\Model\Table\EventsImagesTable
     */
    public $EventsImages;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.events_images',
        'app.images',
        'app.events',
        'app.users',
        'app.categories',
        'app.mailing_list',
        'app.categories_mailing_list',
        'app.series',
        'app.tags',
        'app.events_tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('EventsImages') ? [] : ['className' => EventsImagesTable::class];
        $this->EventsImages = TableRegistry::get('EventsImages', $config);
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
