<?php
namespace App\Test\TestCase\Controller;

use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use PHPUnit\Exception;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Categories',
        'app.Events',
        'app.EventSeries',
        'app.EventsImages',
        'app.EventsTags',
        'app.Images',
        'app.Tags',
        'app.Users',
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => [
                'HTTPS' => 'on',
                'RECAPTCHA_ENABLED' => false,
            ],
        ]);
    }

    /**
     * Tests logging in
     *
     * @return void
     * @throws Exception
     */
    public function testGoodLogin()
    {
        $loginPath = [
            'controller' => 'Users',
            'action' => 'login',
        ];
        $this->get($loginPath);
        $this->assertResponseOk();

        $this->post(
            $loginPath,
            [
                'email' => 'user1@example.com',
                'password' => 'password',
            ]
        );
        $this->assertResponseSuccess();
        $this->assertResponseNotContains('Email or password is incorrect');
        $this->assertSession(1, 'Auth.User.id');
        $this->assertRedirect([
            'controller' => 'Events',
            'action' => 'index',
        ]);
    }

    /**
     * Tests an incorrect login
     *
     * @return void
     * @throws Exception
     */
    public function testBadLogin()
    {
        $this->post(
            [
                'controller' => 'Users',
                'action' => 'login',
            ],
            [
                'email' => 'wrong@example.com',
                'password' => 'wrong',
            ]
        );
        $this->assertResponseOk();
        $this->assertResponseContains('Email or password is incorrect');
        $this->assertSession(null, 'Auth.User.id');
    }

    /**
     * Tests logout
     *
     * @return void
     * @throws Exception
     */
    public function testLogout()
    {
        $this->post(
            [
                'controller' => 'Users',
                'action' => 'login',
            ],
            [
                'email' => 'user1@example.com',
                'password' => 'password',
            ]
        );
        $this->assertSession('user1@example.com', 'Auth.User.email');

        $this->get([
            'controller' => 'Users',
            'action' => 'logout',
        ]);
        $this->assertResponseSuccess();
        $this->assertRedirect([
            'controller' => 'Pages',
            'action' => 'home',
        ]);
        $this->assertSession(null, 'Auth.User.email');
    }

    /**
     * Tests register method
     *
     * @return void
     * @throws Exception
     */
    public function testRegister()
    {
        $registerPath = [
            'controller' => 'Users',
            'action' => 'register',
        ];
        $this->get($registerPath);
        $this->assertResponseOk();

        $email = 'newuser@example.com';
        $this->post($registerPath, [
            'name' => 'New User',
            'email' => $email,
            'password' => 'password',
            'confirm_password' => 'password',
        ]);
        $this->assertRedirect();
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $newUserCount = $usersTable->find()
            ->where(['email' => $email])
            ->count();
        $this->assertEquals(1, $newUserCount);
    }

    /**
     * Tests retrieval of a stored API key
     *
     * @return void
     * @throws Exception
     */
    public function testViewApiKey()
    {
        $userIdWithKey = 1;
        $this->session($this->getUserSession($userIdWithKey));
        $this->get([
            'controller' => 'Users',
            'action' => 'apiKey',
        ]);
        $this->assertResponseOk();

        $usersFixture = new UsersFixture();
        $apiKey = $usersFixture->records[$userIdWithKey - 1]['api_key'];
        $this->assertResponseContains($apiKey);
    }

    /**
     * Tests generation of an API key
     *
     * @return void
     * @throws Exception
     */
    public function testGenerateApiKey()
    {
        $keylessUserId = 2;
        $usersFixture = new UsersFixture();
        $this->assertNull($usersFixture->records[$keylessUserId - 1]['api_key']);

        $this->session($this->getUserSession($keylessUserId));
        $this->post([
            'controller' => 'Users',
            'action' => 'apiKey',
        ]);
        $this->assertResponseOk();

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->get($keylessUserId);
        $this->assertNotNull($user->api_key);

        $this->assertResponseContains($user->api_key);
    }
}
