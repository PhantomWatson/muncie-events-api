<?php
namespace App\Controller;

use App\Event\ApiCallsListener;
use App\Model\Entity\User;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Routing\Router;

class ApiController extends Controller
{

    /**
     * Initialization hook method
     *
     * @return void
     * @throws \Exception
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
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Http\Response|null|void
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
}
