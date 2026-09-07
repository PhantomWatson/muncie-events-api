<?php
namespace App\Test\TestCase\Controller\Admin;

use App\Model\Entity\Event;
use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\LocationsController Test Case
 *
 * @link \App\Controller\Admin\LocationsController
 */
class LocationsControllerTest extends ApplicationTest
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public array $fixtures = [
        'app.Events',
        'app.Users',
        'app.Categories',
        'app.EventSeries',
        'app.Images',
        'app.Tags',
        'app.EventsImages',
        'app.EventsTags',
    ];

    /**
     * Tests that a non-admin user is redirected away from the index page
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexRequiresAdmin()
    {
        $this->session($this->getUserSession(UsersFixture::USER_NOT_SUBSCRIBED));
        $this->get([
            'prefix' => 'Admin',
            'controller' => 'Locations',
            'action' => 'index',
        ]);
        $this->assertRedirect();
    }

    /**
     * Tests that a logged-out visitor is redirected away from the index page
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexRequiresLogin()
    {
        $this->get([
            'prefix' => 'Admin',
            'controller' => 'Locations',
            'action' => 'index',
        ]);
        $this->assertRedirect();
    }

    /**
     * Tests that an admin can view the index page and see the fixtures' location names
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexAsAdmin()
    {
        $this->session($this->getUserSession(UsersFixture::ADMIN_USER));
        $this->get([
            'prefix' => 'Admin',
            'controller' => 'Locations',
            'action' => 'index',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains(EventsFixture::MERGE_LOCATION_A);
        $this->assertResponseContains(EventsFixture::MERGE_LOCATION_B);
    }

    /**
     * Tests that a non-admin user is redirected away from the merge action, and that no events are changed
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMergeRequiresAdmin()
    {
        $this->session($this->getUserSession(UsersFixture::USER_NOT_SUBSCRIBED));
        $this->post(
            [
                'prefix' => 'Admin',
                'controller' => 'Locations',
                'action' => 'merge',
            ],
            [
                'target_location' => EventsFixture::MERGE_LOCATION_A,
                'destination_location' => EventsFixture::MERGE_LOCATION_B,
            ]
        );
        $this->assertRedirect();

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $event */
        $event = $eventsTable->get(EventsFixture::EVENT_AT_MERGE_LOCATION_A);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_A, $event->location);
    }

    /**
     * Tests a successful merge as an admin
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMergeSuccess()
    {
        $this->session($this->getUserSession(UsersFixture::ADMIN_USER));
        $this->post(
            [
                'prefix' => 'Admin',
                'controller' => 'Locations',
                'action' => 'merge',
            ],
            [
                'target_location' => EventsFixture::MERGE_LOCATION_A,
                'destination_location' => EventsFixture::MERGE_LOCATION_B,
            ]
        );
        $this->assertRedirect();

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        foreach ([EventsFixture::EVENT_AT_MERGE_LOCATION_A, EventsFixture::EVENT_AT_MERGE_LOCATION_A_2] as $eventId) {
            /** @var Event $event */
            $event = $eventsTable->get($eventId);
            $this->assertEquals(EventsFixture::MERGE_LOCATION_B, $event->location);
            $this->assertEquals(EventsFixture::MERGE_LOCATION_B_SLUG, $event->location_slug);
        }
    }

    /**
     * Tests that merging is rejected when the target and destination are the same
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMergeFailsWhenTargetAndDestinationAreTheSame()
    {
        $this->session($this->getUserSession(UsersFixture::ADMIN_USER));
        $this->post(
            [
                'prefix' => 'Admin',
                'controller' => 'Locations',
                'action' => 'merge',
            ],
            [
                'target_location' => EventsFixture::MERGE_LOCATION_A,
                'destination_location' => EventsFixture::MERGE_LOCATION_A,
            ]
        );
        $this->assertRedirect();

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $event */
        $event = $eventsTable->get(EventsFixture::EVENT_AT_MERGE_LOCATION_A);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_A, $event->location);
    }

    /**
     * Tests that merging is rejected when either location name is blank
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMergeFailsWithBlankLocation()
    {
        $this->session($this->getUserSession(UsersFixture::ADMIN_USER));
        $this->post(
            [
                'prefix' => 'Admin',
                'controller' => 'Locations',
                'action' => 'merge',
            ],
            [
                'target_location' => '',
                'destination_location' => EventsFixture::MERGE_LOCATION_B,
            ]
        );
        $this->assertRedirect();

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $event */
        $event = $eventsTable->get(EventsFixture::EVENT_AT_MERGE_LOCATION_A);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_A, $event->location);
    }

    /**
     * Tests that merging is rejected when a location name doesn't match any known location
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMergeFailsWithUnknownLocation()
    {
        $this->session($this->getUserSession(UsersFixture::ADMIN_USER));
        $this->post(
            [
                'prefix' => 'Admin',
                'controller' => 'Locations',
                'action' => 'merge',
            ],
            [
                'target_location' => 'Nonexistent Location',
                'destination_location' => EventsFixture::MERGE_LOCATION_B,
            ]
        );
        $this->assertRedirect();

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $event */
        $event = $eventsTable->get(EventsFixture::EVENT_AT_MERGE_LOCATION_B);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_B, $event->location);
    }

    /**
     * Tests that the virtual event location cannot be part of a merge
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMergeFailsWithVirtualLocation()
    {
        $this->session($this->getUserSession(UsersFixture::ADMIN_USER));
        $this->post(
            [
                'prefix' => 'Admin',
                'controller' => 'Locations',
                'action' => 'merge',
            ],
            [
                'target_location' => Event::VIRTUAL_LOCATION,
                'destination_location' => EventsFixture::MERGE_LOCATION_B,
            ]
        );
        $this->assertRedirect();

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        /** @var Event $event */
        $event = $eventsTable->get(EventsFixture::EVENT_AT_MERGE_LOCATION_B);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_B, $event->location);
    }
}
