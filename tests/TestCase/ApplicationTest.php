<?php
namespace App\Test\TestCase;

use App\Application;
use App\Test\Fixture\UsersFixture;
use ArrayAccess;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use PHPUnit\Exception;

/**
 * ApplicationTest class
 */
class ApplicationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var UsersFixture
     */
    protected $usersFixture;

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
            ],
        ]);

        $this->usersFixture = new UsersFixture();
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware()
    {
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $middleware = new MiddlewareQueue();

        $middleware = $app->middleware($middleware);

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(AssetMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->get(2));
    }

    /**
     * Return a session array for the specified user being logged in
     *
     * @param int $userId User ID
     * @return array
     */
    public function getUserSession($userId)
    {
        $usersFixture = new UsersFixture();

        return [
            'Auth' => [
                'User' => $usersFixture->records[$userId - 1],
            ],
        ];
    }

    /**
     * Returns a valid API key
     *
     * @return mixed
     */
    protected function getApiKey()
    {
        return $this->usersFixture->records[0]['api_key'];
    }

    /**
     * Returns a simple array of the IDs of all events returned in the JSON response to the last request
     *
     * @return array|ArrayAccess
     */
    protected function getResponseEventIds()
    {
        $response = (array)json_decode($this->_response->getBody());

        return Hash::extract($response['data'], '{n}.id');
    }

    /**
     * Returns the token associated with the specified user
     *
     * @param int $userId User ID
     * @return string
     * @throws InternalErrorException
     */
    protected function getUserToken($userId = 1)
    {
        foreach ($this->usersFixture->records as $user) {
            if ($user['id'] == $userId) {
                return $user['token'];
            }
        }

        throw new InternalErrorException("User with ID $userId not found in fixture");
    }

    /**
     * Asserts that $methods result in an error when requesting $url
     *
     * @param string|array $url URL of request
     * @param array $methods Array of lowercase request types, e.g. ['get', 'post']
     * @throws Exception
     * @throws InternalErrorException
     */
    protected function assertDisallowedMethods($url, $methods)
    {
        foreach ($methods as $method) {
            switch ($method) {
                case 'get':
                    $this->get($url);
                    break;
                case 'post':
                    $this->post($url);
                    break;
                case 'put':
                    $this->put($url);
                    break;
                case 'patch':
                    $this->patch($url);
                    break;
                case 'delete':
                    $this->delete($url);
                    break;
                default:
                    throw new InternalErrorException('Unrecognized method: ' . $method);
            }
            $this->assertResponseError(strtoupper($method) . ' method is allowed, but should be forbidden');
        }
    }
}
