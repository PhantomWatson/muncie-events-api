<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\WidgetsController Test Case
 *
 * @uses \App\Controller\WidgetsController
 */
class WidgetsControllerTest extends ApplicationTest
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [];

    /**
     * Test index method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndex()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'index',
        ]);
        $this->assertResponseOk();
    }
}
