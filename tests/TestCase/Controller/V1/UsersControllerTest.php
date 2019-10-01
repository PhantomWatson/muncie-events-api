<?php
namespace App\Test\TestCase\Controller\V1;

use App\Auth\LegacyPasswordHasher;
use App\Model\Entity\User;
use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;
use Cake\Utility\Hash;
use PHPUnit\Exception;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends ApplicationTest
{
    use EmailTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Categories',
        'app.Events',
        'app.EventSeries',
        'app.EventsImages',
        'app.EventsTags',
        'app.Images',
        'app.MailingList',
        'app.Tags',
        'app.Users'
    ];

    private $registerUrl;
    private $updatingUserId = 1;
    private $updateProfileUrl;
    private $updatePasswordUrl;

    /**
     * Sets up test object before each test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->updateProfileUrl = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'profile',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken($this->updatingUserId)
            ]
        ];

        $this->updatePasswordUrl = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'password',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken($this->updatingUserId)
            ]
        ];

        $this->registerUrl = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'register',
            '?' => ['apikey' => $this->getApiKey()]
        ];
    }

    /**
     * Method for cleaning up after each test
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        // Clean up previously sent emails for the next test
        TestEmailTransport::clearEmails();
    }

    /**
     * Tests that /user/register succeeds with valid data
     *
     * @return void
     * @throws Exception
     */
    public function testRegisterSuccess()
    {
        $uncleanEmail = 'NewUser@example.com';
        $cleanEmail = 'newuser@example.com';
        $data = [
            'name' => 'New User Name',
            'email' => $uncleanEmail,
            'password' => 'password'
        ];
        $this->post($this->registerUrl, $data);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $attributes = $response['data']->attributes;
        $this->assertNotEmpty($response['data']->id);
        $this->assertEquals($data['name'], $attributes->name);
        $this->assertEquals($cleanEmail, $attributes->email);
        $this->assertNotEmpty($attributes->token);

        $mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        $subscriptionExists = $mailingListTable->exists(['email' => $cleanEmail]);
        $this->assertFalse($subscriptionExists, 'New user shouldn\'t be subscribed to mailing list, but is');
    }

    /**
     * Tests that new users can successfully subscribe to the mailing list as part of registration
     *
     * @throws Exception
     * @return void
     */
    public function testRegisterAndSubscribe()
    {
        $data = [
            'name' => 'New User Name',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'join_mailing_list' => true
        ];
        $this->post($this->registerUrl, $data);
        $this->assertResponseOk();

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        /** @var User $user */
        $user = $usersTable
            ->find()
            ->where(['email' => $data['email']])
            ->first();
        $this->assertNotNull($user->mailing_list_id);

        $mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        $subscriptionExists = $mailingListTable->exists(['email' => $data['email']]);
        $this->assertTrue($subscriptionExists, 'New user should be subscribed to mailing list, but isn\'t');
    }

    /**
     * Tests that /user/register fails for non-POST requests
     *
     * @return void
     * @throws Exception
     */
    public function testRegisterFailBadMethod()
    {
        $this->assertDisallowedMethods($this->registerUrl, ['get', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /user/register fails with missing parameters
     *
     * @return void
     * @throws Exception
     */
    public function testRegisterFailMissingParams()
    {
        $data = [
            'name' => 'New User Name',
            'email' => 'newuser@example.com',
            'password' => 'password'
        ];

        foreach (array_keys($data) as $requiredField) {
            $partialData = $data;
            unset($partialData[$requiredField]);
            $this->post($this->registerUrl, $partialData);
            $this->assertResponseError();
        }
    }

    /**
     * Tests that /user/register fails for nonunique emails
     *
     * @return void
     * @throws Exception
     */
    public function testRegisterFailEmailNonunique()
    {
        $usersFixture = new UsersFixture();
        $email = $usersFixture->records[0]['email'];
        $data = [
            'name' => 'New User Name',
            'email' => $email,
            'password' => 'password'
        ];
        $this->post($this->registerUrl, $data);
        $this->assertResponseError();
    }

    /**
     * Tests successful response from /user/login
     *
     * @return void
     * @throws Exception
     */
    public function testLoginSuccess()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'login',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $usersFixture = new UsersFixture();

        $users = [
            'with new password hash' => $usersFixture->records[0],
            'with legacy password hash' => $usersFixture->records[2]
        ];

        $expectedFields = ['name', 'email', 'token'];
        foreach ($users as $type => $user) {
            $data = [
                'email' => $user['email'],
                'password' => 'password'
            ];
            $this->post($url, $data);
            $response = json_decode($this->_response->getBody())->data;
            $this->assertNotEmpty($response->id);
            foreach ($expectedFields as $expectedField) {
                $this->assertNotEmpty($response->attributes->$expectedField, ucwords($expectedField) . ' is empty');
            }
            $this->assertResponseOk();
        }
    }

    /**
     * Tests error response from /user/login with bad login credentials
     *
     * @return void
     * @throws Exception
     */
    public function testLoginFailBadCredentials()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'login',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $usersFixture = new UsersFixture();

        $data = [
            'email' => $usersFixture->records[0]['email'],
            'password' => 'password'
        ];
        foreach ($data as $field => $val) {
            $wrongData = $data;
            $wrongData[$field] .= 'bad data';
            $this->post($url, $wrongData);
            $this->assertResponseError();
        }
    }

    /**
     * Tests that /user/login fails for non-POST requests
     *
     * @return void
     * @throws Exception
     */
    public function testLoginFailBadMethod()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'login',
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->assertDisallowedMethods($url, ['get', 'put', 'patch', 'delete']);
    }

    /**
     * Tests successful use of /user/{userId}
     *
     * @return void
     * @throws Exception
     */
    public function testViewSuccess()
    {
        $userId = 1;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'view',
            $userId,
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->get($url);
        $this->assertResponseOk();

        $expectedFields = ['name', 'email'];
        $response = json_decode($this->_response->getBody())->data;
        $this->assertNotEmpty($response->id);
        foreach ($expectedFields as $expectedField) {
            $this->assertNotEmpty($response->attributes->$expectedField, ucwords($expectedField) . ' is empty');
        }
    }

    /**
     * Tests that /user/{userId} fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testViewFailBadMethod()
    {
        $userId = 1;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'view',
            $userId,
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->assertDisallowedMethods($url, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /user/{userId} fails for invalid or missing user IDs
     *
     * @return void
     * @throws Exception
     */
    public function testViewFailInvalidUser()
    {
        $userId = 999;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'view',
            $userId,
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->get($url);
        $this->assertResponseError();

        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'view',
            null,
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests that /v1/users/forgot-password returns the correct success status code
     *
     * @return void
     * @throws Exception
     */
    public function testForgotPasswordSuccess()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'forgotPassword',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        /** @var User $user */
        $user = $usersTable->find()->first();
        $this->post($url, ['email' => $user->email]);

        $this->assertResponseCode(204);
        $this->assertMailSentFrom(Configure::read('automailer_address'));
        $this->assertMailSentTo($user->email);
        $resetUrl = 'https://muncieevents.com/reset_password/' . $user->id . '/' . $user->getResetPasswordHash();

        // Slashes need to be escaped because $resetUrl is used as pattern in preg_match("/$resetUrl/")
        $resetUrl = str_replace('/', '\/', $resetUrl);

        $this->assertMailContains($resetUrl);
    }

    /**
     * Tests that /v1/users/forgot-password fails for invalid email addresses
     *
     * @return void
     * @throws Exception
     */
    public function testForgotPasswordFailUnknownUser()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'forgotPassword',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $user = (new UsersFixture())->records[0];
        $this->post($url, ['email' => 'invalid' . $user['email']]);

        $this->assertResponseError();
        $this->assertNoMailSent();
    }

    /**
     * Tests that /v1/users/forgot-password fails if email address is missing or blank
     *
     * @return void
     * @throws Exception
     */
    public function testForgotPasswordFailMissingEmail()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'forgotPassword',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->post($url, ['email' => '']);
        $this->assertResponseError();
        $this->assertNoMailSent();

        $this->post($url, []);
        $this->assertResponseError();
        $this->assertNoMailSent();
    }

    /**
     * Tests that /user/images returns the user's associated images
     *
     * @return void
     * @throws Exception
     */
    public function testImagesSuccess()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'images',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken()
            ]
        ];
        $this->get($url);
        $response = json_decode($this->_response->getBody());

        $expectedImageIds = [1, 2];
        $actualImageIds = Hash::extract($response->data, '{n}.id');
        sort($actualImageIds);
        $this->assertEquals($expectedImageIds, $actualImageIds);

        $expectedAttributeNames = ['full_url', 'small_url', 'tiny_url'];
        $actualAttributeNames = array_keys(get_object_vars($response->data[0]->attributes));
        sort($actualAttributeNames);
        $this->assertEquals($expectedAttributeNames, $actualAttributeNames);
    }

    /**
     * Tests that /user/images returns an empty array if the user has no associated images
     *
     * @return void
     * @throws Exception
     */
    public function testImagesEmpty()
    {
        $userId = 2;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'images',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken($userId)
            ]
        ];
        $this->get($url);
        $response = json_decode($this->_response->getBody());
        $this->assertEmpty($response->data);
    }

    /**
     * Tests that /user/images fails when user token is missing or invalid
     *
     * @return void
     * @throws Exception
     */
    public function testImagesFailBadToken()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'images',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken() . 'invalid'
            ]
        ];
        $this->get($url);
        $this->assertResponseError();

        unset($url['?']['userToken']);
        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests that /user/images fails for non-get requests
     *
     * @return void
     * @throws Exception
     */
    public function testImagesFailBadMethod()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'images',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken()
            ]
        ];

        $this->assertDisallowedMethods($url, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that PATCH /user/profile succeeds with valid parameters
     *
     * @throws Exception
     */
    public function testUpdateProfileSuccess()
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com'
        ];
        $this->patch($this->updateProfileUrl, $data);
        $this->assertResponseCode(204);

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($this->updatingUserId);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
    }

    /**
     * Tests that PATCH /user/profile still succeeds with partial parameters
     *
     * @throws Exception
     */
    public function testPartialUpdateProfileSuccess()
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com'
        ];

        foreach ($data as $param => $value) {
            $alteredData = $data;
            unset($alteredData[$param]);
            $this->patch($this->updateProfileUrl, $alteredData);
            $this->assertResponseOk();
        }
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($this->updatingUserId);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
    }

    /**
     * Tests that /user/profile fails for invalid methods
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateProfileFailBadMethod()
    {
        $this->assertDisallowedMethods($this->updateProfileUrl, ['get', 'put', 'post', 'delete']);
    }

    /**
     * Tests that PATCH /user/profile fails with no name/email data
     *
     * @throws Exception
     */
    public function testUpdateProfileFailNoParams()
    {
        $this->patch($this->updateProfileUrl, []);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/profile fails with blank name/email
     *
     * @throws Exception
     */
    public function testUpdateProfileFailBlankParams()
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com'
        ];

        foreach ($data as $param => $value) {
            $alteredData = $data;
            $alteredData[$param] = '';
            $this->patch($this->updateProfileUrl, $alteredData);
            $this->assertResponseError("Error not triggered for blank $param");
        }
    }

    /**
     * Tests that PATCH /user/profile fails with no user token
     *
     * @throws Exception
     */
    public function testUpdateProfileFailNoUser()
    {
        $url = $this->updateProfileUrl;
        unset($url['?']['userToken']);
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com'
        ];
        $this->patch($url, $data);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/profile fails with an invalid user token
     *
     * @throws Exception
     */
    public function testUpdateProfileFailInvalidUser()
    {
        $url = $this->updateProfileUrl;
        $url['?']['userToken'] .= 'invalid';
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com'
        ];
        $this->patch($url, $data);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/profile fails with non-unique email
     *
     * @throws Exception
     */
    public function testUpdateProfileFailNonuniqueEmail()
    {
        $usersFixture = new UsersFixture();
        $lastUser = end($usersFixture->records);
        $data = [
            'name' => 'Updated Name',
            'email' => $lastUser['email']
        ];
        $this->patch($this->updateProfileUrl, $data);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/password succeeds with valid parameters
     *
     * @throws Exception
     */
    public function testUpdatePasswordSuccess()
    {
        $data = ['password' => 'new password'];
        $this->patch($this->updatePasswordUrl, $data);
        $this->assertResponseCode(204);

        $passwordHash = (new LegacyPasswordHasher)->hash($data['password']);
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $userIsUpdated = $usersTable->exists([
            'id' => $this->updatingUserId,
            'password' => $passwordHash
        ]);
        $this->assertTrue($userIsUpdated, 'Password was not updated.');
    }

    /**
     * Tests that /user/password fails for invalid methods
     *
     * @return void
     * @throws Exception
     */
    public function testUpdatePasswordFailBadMethod()
    {
        $this->assertDisallowedMethods($this->updatePasswordUrl, ['get', 'put', 'post', 'delete']);
    }

    /**
     * Tests that PATCH /user/password fails with no password data
     *
     * @throws Exception
     */
    public function testUpdatePasswordFailNoParams()
    {
        $this->patch($this->updatePasswordUrl, []);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/password fails with blank password
     *
     * @throws Exception
     */
    public function testUpdatePasswordFailBlankPassword()
    {
        $this->patch($this->updatePasswordUrl, ['password' => '']);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/password fails with no user token
     *
     * @throws Exception
     */
    public function testUpdatePasswordFailNoUser()
    {
        $url = $this->updatePasswordUrl;
        unset($url['?']['userToken']);
        $this->patch($url, ['password' => 'new password']);
        $this->assertResponseError();
    }

    /**
     * Tests that PATCH /user/password fails with an invalid user token
     *
     * @throws Exception
     */
    public function testUpdatePasswordFailInvalidUser()
    {
        $url = $this->updatePasswordUrl;
        $url['?']['userToken'] .= 'invalid';
        $this->patch($url, ['password' => 'new password']);
        $this->assertResponseError();
    }

    /**
     * Tests that GET /user/{userId}/events succeeds
     *
     * @throws Exception
     */
    public function testEventsSuccess()
    {
        $userId = 1;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'events',
            $userId,
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->get($url);
        $this->assertResponseOk();

        $response = json_decode($this->_response->getBody());
        $this->assertNotEmpty($response->links->first);
        $this->assertNotEmpty($response->links->last);
        $this->assertNotEmpty($response->data);
        $userIds = Hash::extract($response->data, '{n}.relationships.user.data.id');
        $userIds = array_unique($userIds);
        $this->assertEquals([$userId], $userIds);
    }

    /**
     * Tests that GET /user/{userId}/events fails for invalid methods
     *
     * @return void
     * @throws Exception
     */
    public function testEventsFailBadMethod()
    {
        $userId = 1;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'events',
            $userId,
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->assertDisallowedMethods($url, ['patch', 'put', 'post', 'delete']);
    }

    /**
     * Tests that GET /user/{userId}/events fails for invalid user IDs
     *
     * @return void
     * @throws Exception
     */
    public function testEventsFailBadUserId()
    {
        $userId = 999;
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'events',
            $userId,
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->get($url);
        $this->assertResponseError();
    }
}
