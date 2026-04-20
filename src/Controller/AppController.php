<?php
namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Table\EventsTable;
use Authentication\Controller\Component\AuthenticationComponent;
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
 * @property \App\Model\Table\EventsTable $Events
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
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

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication', [
            'logoutRedirect' => '/login',
        ]);
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

        if (!$this->request->is('https') && Configure::read('redirectToHttps')) {
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
        $authUser = $this->getAuthUser();
        $this->set([
            'authUser' => $authUser,
            'unapprovedCount' => $authUser ? $this->Events->getUnapprovedCount() : 0,
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

    /**
     * Returns the User entity for the currently logged-in user, or null if the user is unauthenticated
     *
     * @return User|null
     */
    protected function getAuthUser(): ?User
    {
        return $this->Authentication->getIdentity()?->getOriginalData();
    }
}
