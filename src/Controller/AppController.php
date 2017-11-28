<?php
namespace App\Controller;

use App\Model\Entity\User;
use Cake\Controller\Controller;
use Cake\Event\Event;

class AppController extends Controller
{

    /**
     * Initialization hook method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent(
            'Auth',
            [
                'loginAction' => [
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'login'
                ],
                'logoutRedirect' => [
                    'prefix' => false,
                    'controller' => 'Pages',
                    'action' => 'index'
                ],
                'authenticate' => [
                    'Form' => [
                        'fields' => [
                            'username' => 'email',
                            'password' => 'password'
                        ]
                    ],
                    'Xety/Cake3CookieAuth.Cookie'
                ],
                'authError' => 'You are not authorized to view this page',
                'authorize' => 'Controller'
            ]
        );
        $this->loadComponent('Cookie');
        $this->Auth->deny();
    }

    /**
     * beforeFilter method
     *
     * @param Event $event CakePHP event object
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        if (!$this->Auth->user() && $this->request->getCookie('CookieAuth')) {
            $this->loadModel('Users');
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
            } else {
                $this->response = $this->response->withCookie('CookieAuth', null);
            }
        }
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        $this->set([
            'authUser' => $this->Auth->user()
        ]);
    }

    /**
     * isAuthorized method
     *
     * @param User $user User entity
     * @return bool
     */
    public function isAuthorized($user)
    {
        if (!$this->request->getParam('prefix')) {
            return true;
        }

        return false;
    }
}
