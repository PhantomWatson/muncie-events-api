<?php
namespace App\Test\TestCase\Controller\V1;

use App\Model\Entity\MailingList;
use App\Model\Table\MailingListTable;
use App\Model\Table\UsersTable;
use App\Test\Fixture\MailingListFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * MailingListController Test Case
 */
class MailingListControllerTest extends ApplicationTest
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Categories',
        'app.MailingList',
        'app.Users'
    ];

    /** @var UsersTable */
    private $usersTable;

    /** @var MailingListTable */
    private $mailingListTable;

    private $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    private $defaultData = [];
    private $fixedEmail = 'new_subscriber@example.com';
    private $unfixedEmail = 'NEW_SUBSCRIBER@example.com ';
    private $subscribeUrl;

    /**
     * Cleans up tests after they've completed
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->subscribeUrl = [
            'prefix' => 'v1',
            'controller' => 'MailingList',
            'action' => 'subscribe',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken(1)
            ]
        ];
        $this->usersTable = TableRegistry::getTableLocator()->get('Users');
        $this->mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        $this->defaultData = [
            'email' => $this->unfixedEmail,
            'weekly' => true,
            'daily' => false,
            'all_categories' => true
        ];
        foreach ($this->days as $day) {
            $this->defaultData["daily_$day"] = false;
        }
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe returns the correct results for a logged-in user
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccessLoggedIn()
    {
        $subscribed = $this->mailingListTable->exists(['email' => $this->fixedEmail]);
        $this->assertFalse($subscribed, 'User is already subscribed before request');

        $this->post($this->subscribeUrl, $this->defaultData);
        $this->assertResponseCode(204);

        $newSubscription = $this->getNewSubscription();
        $this->assertDefaultDataSaved($newSubscription);
        $associationWasMade = $this->usersTable->exists([
            'id' => 1,
            'mailing_list_id' => $newSubscription->id
        ]);
        $this->assertTrue($associationWasMade, 'User was not associated with mailing list subscription');
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe succeeds for an anonymous user using an unrecognized email address
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccessAnonymousNewEmail()
    {
        $url = $this->subscribeUrl;
        unset($url['?']['userToken']);

        $subscribed = $this->mailingListTable->exists(['email' => $this->fixedEmail]);
        $this->assertFalse($subscribed, 'User is already subscribed before request');

        $this->post($url, $this->defaultData);
        $this->assertResponseCode(204);

        $newSubscription = $this->getNewSubscription();
        $this->assertDefaultDataSaved($newSubscription);
        $associatedUser = $this->usersTable
            ->find()
            ->where(['mailing_list_id' => $newSubscription->id])
            ->first();
        $associatedUserId = $associatedUser ? $associatedUser->id : null;
        $this->assertNull(
            $associatedUser,
            sprintf(
                'User (%s) was associated with this subscription (%s) when none should be',
                $associatedUserId,
                $newSubscription->id
            )
        );
    }

    /**
     * Returns the most recently-added record to the MailingList table
     *
     * @return MailingList
     */
    private function getNewSubscription()
    {
        /** @var MailingList $subscription */
        $subscription = $this->mailingListTable
            ->find()
            ->orderDesc('id')
            ->first();

        return $subscription;
    }

    /**
     * Runs assertions on the default set of request data
     *
     * @param MailingList $newSubscription Most recently added mailing list record
     * @return void
     */
    private function assertDefaultDataSaved($newSubscription)
    {
        $this->assertNotEmpty($newSubscription, 'Subscription was not added');
        $this->assertEquals($this->fixedEmail, $newSubscription->email);
        $this->assertTrue((bool)$newSubscription->weekly);
        $this->assertTrue((bool)$newSubscription->all_categories);
        foreach ($this->days as $day) {
            $this->assertFalse((bool)$newSubscription->{"daily_$day"});
        }
    }

    /**
     * Tests that /v1/mailing-list/subscribe fails for non-post requests
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddFailBadMethod()
    {
        $this->assertDisallowedMethods($this->subscribeUrl, ['get', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe fails if the email address is already subscribed
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddFailAlreadySubscribed()
    {
        $subscription = (new MailingListFixture())->records[0];
        $data = $this->defaultData;
        $data['email'] = $subscription['email'];

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseError('Error was not generated for subscribing an already-subscribed email address');
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe fails if no email address is provided
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddFailMissingEmail()
    {
        $data = $this->defaultData;
        unset($data['email']);

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseError('Error was not generated for missing email address');
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe fails if no category selection is provided
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddFailMissingCategorySelection()
    {
        $data = $this->defaultData;
        unset($data['all_categories']);
        unset($data['category_ids']);

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseError('Error was not generated for missing category info');
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe fails if no frequency selection is provided
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddFailMissingFrequencySelection()
    {
        $data = $this->defaultData;
        unset($data['weekly']);
        unset($data['daily']);
        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        foreach ($days as $day) {
            unset($data["daily_$day"]);
        }

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseError('Error was not generated for missing frequency info');
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe succeeds for daily instead of weekly emails
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccessDaily()
    {
        $data = $this->defaultData;
        $data['weekly'] = false;
        $data['daily'] = true;

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseCode(204);

        $newSubscription = $this->getNewSubscription();
        foreach ($this->days as $day) {
            $this->assertTrue((bool)$newSubscription->{"daily_$day"});
        }
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe succeeds for subscribing on specific days
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccessCustomDays()
    {
        foreach ($this->days as $selectedDay) {
            $data = $this->defaultData;
            $data['email'] = $selectedDay . $data['email'];
            $data['weekly'] = false;
            $data['daily'] = false;
            foreach ($this->days as $day) {
                $data["daily_$day"] = ($day == $selectedDay);
            }

            $this->post($this->subscribeUrl, $data);
            $this->assertResponseCode(204, "Error code thrown when trying to subscribe only on $selectedDay");

            $newSubscription = $this->getNewSubscription();
            foreach ($this->days as $day) {
                $isSelected = (bool)$newSubscription->{"daily_$day"};
                if ($day == $selectedDay) {
                    $this->assertTrue($isSelected);
                } else {
                    $this->assertFalse($isSelected);
                }
            }
        }
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe succeeds for subscribing on specific days and weekly
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccessCustomDaysAndWeekly()
    {
        $data = $this->defaultData;
        $data['weekly'] = true;
        $data['daily'] = false;
        foreach ($this->days as $day) {
            $data["daily_$day"] = in_array($day, ['sat', 'sun']);
        }

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseCode(204);

        $newSubscription = $this->getNewSubscription();
        foreach ($this->days as $day) {
            $isSelected = (bool)$newSubscription->{"daily_$day"};
            if (in_array($day, ['sat', 'sun'])) {
                $this->assertTrue($isSelected);
            } else {
                $this->assertFalse($isSelected);
            }
        }
        $this->assertTrue((bool)$newSubscription->weekly);
    }

    /**
     * Tests that 'daily' overrides specific days for POST /v1/mailing-list/subscribe
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddDailyAndCustomDays()
    {
        $data = $this->defaultData;
        $data['daily'] = true;
        foreach ($this->days as $day) {
            $data["daily_$day"] = in_array($day, ['sat', 'sun']);
        }

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseCode(204);

        $newSubscription = $this->getNewSubscription();
        foreach ($this->days as $day) {
            $isSelected = (bool)$newSubscription->{"daily_$day"};
            $this->assertTrue($isSelected);
        }
    }

    /**
     * Tests that POST /v1/mailing-list/subscribe succeeds for subscribing daily and weekly
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccessDailyAndWeekly()
    {
        $data = $this->defaultData;
        $data['weekly'] = true;
        $data['daily'] = true;

        $this->post($this->subscribeUrl, $data);
        $this->assertResponseCode(204);

        $newSubscription = $this->getNewSubscription();
        foreach ($this->days as $day) {
            $isSelected = (bool)$newSubscription->{"daily_$day"};
            $this->assertTrue($isSelected);
        }
        $this->assertTrue((bool)$newSubscription->weekly);
    }
}
