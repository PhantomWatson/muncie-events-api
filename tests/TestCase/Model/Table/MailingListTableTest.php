<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MailingListTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MailingListTable Test Case
 */
class MailingListTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var MailingListTable
     */
    public $MailingList;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.MailingList',
        'app.Users',
        'app.Categories',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('MailingList') ? [] : ['className' => MailingListTable::class];
        $this->MailingList = TableRegistry::getTableLocator()->get('MailingList', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MailingList);

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
