<?php
namespace App\Auth;

use App\Model\Table\UsersTable;
use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

class ApiKeyAuthenticate extends BaseAuthenticate
{
    private $apiKeyFields = ['apikey', 'apiKey'];

    /**
     * Constructor method
     *
     * @param ComponentRegistry $registry The Component registry used on this request.
     * @param array $config Array of config to use.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
    }

    /**
     * Authenticate a user based on the request information.
     *
     * @param ServerRequest $request Request to get authentication information from.
     * @param Response $response A response object that can have headers added.
     * @return mixed Either false on failure, or an array of user data on success.
     */
    public function authenticate(ServerRequest $request, Response $response)
    {
        return $this->getUser($request);
    }

    /**
     * Get a user based on information in the request
     *
     * @param ServerRequest $request Request object.
     * @return mixed Either false or an array of user information
     */
    public function getUser(ServerRequest $request)
    {
        $apiKey = $this->getApiKey($request);
        if (empty($apiKey)) {
            return false;
        }

        /** @var UsersTable $usersTable */
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->findByApiKey($apiKey)->first();

        return $user ? $user->toArray() : false;
    }

    /**
     * Handle unauthenticated access attempt. In implementation valid return values
     * can be:
     *
     * - Null - No action taken, AuthComponent should return appropriate response.
     * - Cake\Http\Response - A response object, which will cause AuthComponent to
     *   simply return that response.
     *
     * @param ServerRequest $request A request object.
     * @param Response $response A response object.
     * @return void
     */
    public function unauthenticated(ServerRequest $request, Response $response)
    {
        $apiKey = $this->getApiKey($request);
        if ($apiKey) {
            throw new UnauthorizedException('Api key not recognized');
        }

        throw new UnauthorizedException('Api key not provided');
    }

    /**
     * Returns the API key passed in the current request
     *
     * @param ServerRequest $request Request object
     * @return string|null
     */
    private function getApiKey(ServerRequest $request)
    {
        foreach ($this->apiKeyFields as $apiKeyField) {
            $apiKey = $request->getQuery($apiKeyField);
            if ($apiKey) {
                return $apiKey;
            }
        }

        return null;
    }
}
