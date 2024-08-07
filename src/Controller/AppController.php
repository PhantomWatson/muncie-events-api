<?php
namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Table\EventsTable;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\ORM\Table;
use Exception;

/**
 * Class AppController
 *
 * @package App\Controller
 * @property EventsTable $Events
 */
class AppController extends Controller
{
    protected EventsTable|Table $Events;

    /**
     * Initialization hook method
     *
     * @return Response|null
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        // Make $this->Events available for all controllers
        $this->Events = $this->fetchTable('Events');

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
    }

    /**
     * beforeFilter method
     *
     * @param Event $event CakePHP event object
     * @return Response|null
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        /* Fix weird 404 error caused by cPanel's redirection of https://themunciescene.com/?fbclid=...
         * MuncieEvents.com resulting in the ? being encoded */
        $target = $this->getRequest()->getRequestTarget();
        $pattern = '/^%3Ffbclid|^\?fbclid|^fbclid/';
        if (preg_match($pattern, $target)) {
            return $this->redirect('/');
        }

        if (!$this->request->is('ssl') && Configure::read('redirectToHttps')) {
            return $this->redirect('https://' . env('SERVER_NAME') . $this->request->getRequestTarget());
        }

        // Maintenance mode - regular pages
        if ($this->shouldRedirectRequestToMaintenanceMode()) {
            $this->setResponse($this->getResponse()->withStatus(503));
            return $this->redirect([
                'controller' => 'Pages',
                'action' => 'maintenanceMode',
            ]);
        }

        // Maintenance mode - API endpoints that only need an empty 503 response
        if ($this->shouldReturnMaintenanceModeStatus()) {
            return $this->getResponse()->withStatus(503);
        }

        // Maintenance mode - Flash message for non-redirected pages
        if (Configure::read('maintenanceMode')) {
            $this->Flash->set(
                'Muncie Events is currently undergoing maintenance, and some functions will be temporarily unavailable'
            );
        }

        if (!$this->Auth->user() && $this->request->getCookie('CookieAuth')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
            } else {
                $this->response = $this->response->withExpiredCookie(new Cookie('CookieAuth'));
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
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
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
            'enable' => Configure::read('Recaptcha.enabled', true),
            'sitekey' => Configure::read('Recaptcha.siteKey'),
            'secret' => Configure::read('Recaptcha.secret'),
            'type' => 'image',
            'theme' => 'light',
            'lang' => 'en',
            'size' => 'normal',
        ]);
    }

    /**
     * Returns TRUE if the site is in maintenance mode and the requested page should be redirected
     *
     * @return bool
     */
    private function shouldRedirectRequestToMaintenanceMode(): bool
    {
        if (!Configure::read('maintenanceMode')) {
            return false;
        }
        $controller = $this->getRequest()->getParam('controller');
        $action = $this->getRequest()->getParam('action');
        switch ("$controller.$action") {
            case 'Events.add':
            case 'Events.edit':
            case 'Events.delete':
            case 'MailingList.index':
            case 'MailingList.unsubscribe':
            case 'Users.changePass':
            case 'Users.register':
            case 'Users.resetPassword':
                return true;
            default:
                return false;
        }
    }

    private function shouldReturnMaintenanceModeStatus()
    {
        if (!Configure::read('maintenanceMode')) {
            return false;
        }
        $controller = $this->getRequest()->getParam('controller');
        $action = $this->getRequest()->getParam('action');
        switch ("$controller.$action") {
            case 'Images.upload':
                return true;
            default:
                return false;
        }
    }
}
