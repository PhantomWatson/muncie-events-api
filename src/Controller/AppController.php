<?php
namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Table\EventsTable;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\Response;
use Exception;

/**
 * Class AppController
 *
 * @package App\Controller
 * @property EventsTable $Events
 */
class AppController extends Controller
{

    /**
     * Initialization hook method
     *
     * @return Response|null
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        $this->loadComponent(
            'Auth',
            [
                'loginAction' => [
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'login',
                ],
                'logoutRedirect' => [
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'index',
                ],
                'authenticate' => [
                    'Form' => [
                        'fields' => [
                            'username' => 'email',
                            'password' => 'password',
                        ],
                        'passwordHasher' => [
                            'className' => 'Fallback',
                            'hashers' => [
                                'Default',
                                'Weak' => ['hashType' => 'sha1'],
                            ],
                        ],
                    ],
                    'Cookie' => [
                        'fields' => [
                            'username' => 'email',
                            'password' => 'password',
                        ],
                    ],
                ],
                'authError' => 'You are not authorized to view this page',
                'authorize' => 'Controller',
            ]
        );
        $this->Auth->deny();

        if (!$this->request->is('ssl')) {
            return $this->redirect('https://' . env('SERVER_NAME') . $this->request->getRequestTarget());
        }

        return null;
    }

    /**
     * beforeFilter method
     *
     * @param Event $event CakePHP event object
     * @return Response|null
     */
    public function beforeFilter(Event $event)
    {
        /* Fix weird 404 error caused by cPanel's redirection of https://themunciescene.com/?fbclid=...
         * MuncieEvents.com resulting in the ? being encoded */
        $target = $this->getRequest()->getRequestTarget();
        $pattern = '/^%3Ffbclid|^\?fbclid|^fbclid/';
        if (preg_match($pattern, $target)) {
            return $this->redirect('/');
        }

        if (!$this->Auth->user() && $this->request->getCookie('CookieAuth')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
            } else {
                $this->response = $this->response->withExpiredCookie('CookieAuth');
            }
        }

        // Replace "You are not authorized" error message with login prompt message if user is not logged in
        if (!$this->Auth->user()) {
            $this->Auth->setConfig('authError', 'You\'ll need to log in before accessing that page');
        }

        return null;
    }

    /**
     * Before render callback.
     *
     * @param Event $event The beforeRender event.
     * @return Response|null|void
     */
    public function beforeRender(Event $event)
    {
        $this->loadModel('Events');
        $this->set([
            'authUser' => $this->Auth->user(),
            'unapprovedCount' => $this->Auth->user() ? $this->Events->getUnapprovedCount() : 0,
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

    /**
     * Loads the Recaptcha component
     *
     * @throws \Exception
     * @return void
     */
    protected function loadRecaptcha()
    {
        $this->loadComponent('Recaptcha.Recaptcha', [
            'enable' => (bool)$this->request->getEnv('RECAPTCHA_ENABLED', true),
            'sitekey' => env('RECAPTCHA_SITE_KEY'),
            'secret' => env('RECAPTCHA_SECRET'),
            'type' => 'image',
            'theme' => 'light',
            'lang' => 'en',
            'size' => 'normal',
        ]);
    }
}
