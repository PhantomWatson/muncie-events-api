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
     * Tests that POST /v1/mailing-list/subscribe returns the correct results for a logged-in user
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
}
