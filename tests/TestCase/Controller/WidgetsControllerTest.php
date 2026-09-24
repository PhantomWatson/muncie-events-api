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
    public array $fixtures = [
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

    /**
     * Tests that malicious style values are not reflected into the month widget
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMonthRejectsMaliciousStyles()
    {
        $payload = 'red;}</style><svg/onload=alert(1)>';
        $this->get([
            'controller' => 'Widgets',
            'action' => 'month',
            '?' => [
                'backgroundColorAlt' => '#fffdc6',
                'fontSize' => $payload,
                'textColorLink' => $payload,
            ],
        ]);
        $this->assertResponseOk();
        $this->assertResponseNotContains('<svg/onload');
        $this->assertResponseContains('background-color: #fffdc6;');
    }

    /**
     * Tests that a malicious location filter is escaped in the feed widget
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testFeedEscapesLocationFilter()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'feed',
            '?' => ['location' => '<script>alert(1)</script>'],
        ]);
        $this->assertResponseOk();
        $this->assertResponseNotContains('<script>alert(1)</script>');
        $this->assertResponseContains('&lt;script&gt;alert(1)&lt;/script&gt;');
    }

    /**
     * Tests that a malicious border color is not reflected into the customization page's iframe styles
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testCustomizeMonthRejectsMaliciousBorderColor()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'customizeMonth',
            '?' => ['borderColorDark' => 'red" onload="alert(1)'],
        ]);
        $this->assertResponseOk();
        $this->assertResponseNotContains('onload="alert(1)');
    }

    /**
     * Tests that array-valued options fall back to defaults instead of causing an error
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMonthIgnoresArrayOptions()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'month',
            '?' => [
                'events_displayed_per_day' => ['5'],
                'textColorLink' => ['#ff0000'],
            ],
        ]);
        $this->assertResponseOk();
        $this->assertResponseNotContains('#ff0000');
    }
}
