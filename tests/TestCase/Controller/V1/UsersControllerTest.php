<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\Fixture\UsersFixture;
use App\Test\TestCase\ApplicationTest;

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
        'app.ApiCalls',
        'app.Categories',
        'app.EventSeries',
        'app.Events',
        'app.EventsImages',
        'app.EventsTags',
        'app.Images',
        'app.Tags',
        'app.Users'
    ];

    /**
     * Tests that /user/register succeeds with valid data
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRegisterSuccess()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'register',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $data = [
            'name' => 'New User Name',
            'email' => 'newuser@example.com',
            'password' => 'password'
        ];
        $this->post($url, $data);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $attributes = $response['data']->attributes;
        $this->assertNotEmpty($response['data']->id);
        $this->assertEquals($data['name'], $attributes->name);
        $this->assertEquals($data['email'], $attributes->email);
        $this->assertNotEmpty($attributes->token);
    }

    /**
     * Tests that /user/register fails for non-POST requests
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRegisterFailBadMethod()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'register',
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->get($url);
        $this->assertResponseError();

        $this->put($url);
        $this->assertResponseError();

        $this->patch($url);
        $this->assertResponseError();

        $this->delete($url);
        $this->assertResponseError();
    }

    /**
     * Tests that /user/register fails with missing parameters
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRegisterFailMissingParams()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'register',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $data = [
            'name' => 'New User Name',
            'email' => 'newuser@example.com',
            'password' => 'password'
        ];

        foreach (array_keys($data) as $requiredField) {
            $partialData = $data;
            unset($partialData[$requiredField]);
            $this->post($url, $partialData);
            $this->assertResponseError();
        }
    }

    /**
     * Tests that /user/register fails for nonunique emails
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRegisterFailEmailNonunique()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'register',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $usersFixture = new UsersFixture();
        $email = $usersFixture->records[0]['email'];
        $data = [
            'name' => 'New User Name',
            'email' => $email,
            'password' => 'password'
        ];
        $this->post($url, $data);
        $this->assertResponseError();
    }

    /**
     * Tests successful response from /user/login
     *
     * @return void
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
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
     * @throws \PHPUnit\Exception
     */
    public function testLoginFailBadMethod()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Users',
            'action' => 'login',
            '?' => ['apikey' => $this->getApiKey()]
        ];

        $this->get($url);
        $this->assertResponseError();

        $this->put($url);
        $this->assertResponseError();

        $this->patch($url);
        $this->assertResponseError();

        $this->delete($url);
        $this->assertResponseError();
    }
}