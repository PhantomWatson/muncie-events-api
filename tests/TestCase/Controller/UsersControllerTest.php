<?php
namespace App\Test\TestCase\Controller;

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
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    /**
     * Tests logging in
     *
     * @return void
     */
    public function testGoodLogin()
    {
        $loginPath = [
            'controller' => 'Users',
            'action' => 'login'
        ];
        $this->get($loginPath);
        $this->assertResponseOk();

        $this->post($loginPath,
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
}
