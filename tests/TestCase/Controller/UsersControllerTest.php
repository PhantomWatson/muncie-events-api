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
        $this->get('/login');
        $this->assertResponseOk();

        $this->post('/login', [
            'email' => 'user1@example.com',
            'password' => 'password'
        ]);
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
        $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong'
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Email or password is incorrect');
        $this->assertSession(null, 'Auth.User.id');
    }
}
