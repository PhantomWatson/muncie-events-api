<?php
namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * UsersControllerTest class
 */
class UsersControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.users'
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
                'RECAPTCHA_ENABLED' => false
            ]
        ]);
    }

    /**
     * Tests logging in
     *
     * @return void
     */
    public function testGoodLogin()
    {
        $this->_request['environment']['FOO'] = 'bar';
        $loginPath = [
            'controller' => 'Users',
            'action' => 'login'
        ];
        $this->get($loginPath);
        $this->assertResponseOk();

        $this->post(
            $loginPath,
            [
                'email' => 'user1@example.com',
                'password' => 'password'
            ]
        );
        $this->assertResponseSuccess();
        $this->assertResponseNotContains('Email or password is incorrect');
        $this->assertSession('1', 'Auth.User.id');
        $this->assertRedirect([
            'controller' => 'Pages',
            'action' => 'home'
        ]);
    }

    /**
     * Tests an incorrect login
     *
     * @return void
     */
    public function testBadLogin()
    {
        $this->post(
            [
                'controller' => 'Users',
                'action' => 'login'
            ],
            [
                'email' => 'wrong@example.com',
                'password' => 'wrong'
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
     */
    public function testLogout()
    {
        $this->post(
            [
                'controller' => 'Users',
                'action' => 'login'
            ],
            [
                'email' => 'user1@example.com',
                'password' => 'password'
            ]
        );
        $this->assertSession('user1@example.com', 'Auth.User.email');

        $this->get([
            'controller' => 'Users',
            'action' => 'logout'
        ]);
        $this->assertResponseSuccess();
        $this->assertRedirect([
            'controller' => 'Pages',
            'action' => 'home'
        ]);
        $this->assertSession(null, 'Auth.User.email');
    }

    /**
     * Tests register method
     *
     * @return void
     */
    public function testRegister()
    {
        $registerPath = [
            'controller' => 'Users',
            'action' => 'register'
        ];
        $this->get($registerPath);
        $this->assertResponseOk();

        $email = 'newuser@example.com';
        $this->post($registerPath, [
            'name' => 'New User',
            'email' => $email,
            'password' => 'password',
            'confirm_password' => 'password'
        ]);
        $this->assertRedirect();
        $usersTable = TableRegistry::get('Users');
        $newUserCount = $usersTable->find()
            ->where(['email' => $email])
            ->count();
        $this->assertEquals(1, $newUserCount);
    }
}
