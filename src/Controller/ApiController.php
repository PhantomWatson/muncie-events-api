<?php
namespace App\Controller;

use App\Event\ApiCallsListener;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Exception;

class ApiController extends Controller
{
    /**
     * An array of user information for the user identified by the user token provided in request data
     * (distinct from the user identified by the API key)
     *
     * @var User|null
     */
    protected $tokenUser;

    /**
     * Initialization hook method
     *
     * @return void
     * @throws BadRequestException
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false
        ]);
        if (!$this->request->is('ssl')) {
            throw new BadRequestException('API calls must be made with HTTPS protocol');
        }

        if (!$this->isApiSubdomain()) {
            throw new BadRequestException('API calls must be made on the api subdomain');
        }

        $this->loadComponent(
            'Auth',
            [
                'authenticate' => ['ApiKey'],
                'authError' => 'You are not authorized to view this page',
                'authorize' => 'Controller'
            ]
        );
        $this->Auth->deny();

        $apiCallsListener = new ApiCallsListener();
        EventManager::instance()->on($apiCallsListener);

        $this->viewBuilder()->setClassName('JsonApi.JsonApi');

        $this->set('_url', Router::url('/v1', true));
    }

    /**
     * beforeFilter method
     *
     * @param Event $event CakePHP event object
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        if ($this->request->getQuery('userToken')) {
            $this->tokenUser = $this->getTokenUser();
        }
    }

    /**
     * Returns TRUE if the current request is made on a valid API subdomain
     *
     * @return bool
     */
    private function isApiSubdomain()
    {
        $host = $this->request->host();

        return stripos($host, 'api.') === 0;
    }

    /**
     * Before render callback.
     *
     * @param Event $event The beforeRender event.
     * @return Response|null|void
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
    }

    /**
     * After filter callback
     *
     * @param Event $event The afterFilter event
     * @return void
     */
    public function afterFilter(Event $event)
    {
        parent::afterFilter($event);

        $event = new Event('apiCall', $this, ['meta' => [
            'url' => $this->request->getRequestTarget(),
            'userId' => $this->Auth->user('id')
        ]]);
        $this->getEventManager()->dispatch($event);
    }

    /**
     * isAuthorized method
     *
     * @param User $user User entity
     * @return bool
     */
    public function isAuthorized($user)
    {
        return true;
    }

    /**
     * Returns the user identified by the token provided in the query string
     *
     * @return User
     * @throws BadRequestException
     */
    private function getTokenUser()
    {
        $token = $this->request->getQuery('userToken');
        /** @var UsersTable $usersTable */
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->getByToken($token);

        if (!$user) {
            throw new BadRequestException('User token invalid');
        }

        return $user;
    }

    /**
     * Returns a 204 "No Content" response
     *
     * @return void
     */
    protected function set204Response()
    {
        $this->response = $this->response->withStatus(204, 'No Content');

        /* Bypass JsonApi plugin to render blank response,
         * as required by the JSON API standard (https://jsonapi.org/format/#crud-creating-responses-204) */
        $this->viewBuilder()->setClassName('Json');
        $this->set('_serialize', true);
    }
}
