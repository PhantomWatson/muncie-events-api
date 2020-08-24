<?php
namespace App\Controller;

use App\Model\Table\UsersTable;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;
use Recaptcha\Controller\Component\RecaptchaComponent;

/**
 * Class UsersController
 * @package App\Controller
 * @property UsersTable $Users
 * @property RecaptchaComponent $Recaptcha
 */
class UsersController extends AppController
{
    /**
     * Initialize method
     *
     * @return Response|null
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        if (!$this->request->is('ssl')) {
            return $this->redirect('https://' . env('SERVER_NAME') . $this->request->getRequestTarget());
        }

        $this->loadComponent('Recaptcha.Recaptcha', [
            'enable' => (bool)$this->request->getEnv('RECAPTCHA_ENABLED', true),
            'sitekey' => '6LeDpjoUAAAAADAE8vX2DOVuuRYQSmSqRhvxIr5G',
            'secret' => env('RECAPTCHA_SECRET'),
            'type' => 'image',
            'theme' => 'light',
            'lang' => 'en',
            'size' => 'normal',
        ]);

        $this->Auth->allow([
            'forgotPassword',
            'login',
            'logout',
            'register',
        ]);

        return null;
    }

    /**
     * Register page
     *
     * @return Response|null
     */
    public function register()
    {
        $user = $this->Users->newEntity();

        $this->set([
            'pageTitle' => 'Register an Account',
            'user' => $user,
        ]);

        if (!$this->request->is('post')) {
            return null;
        }

        if ($this->Recaptcha->verify()) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['name', 'email', 'password'],
            ]);
            $user->role = 'user';

            if ($this->Users->save($user)) {
                $this->Flash->success('Registration successful');
                $this->Auth->setUser($user);

                return $this->redirect([
                    'controller' => 'Events',
                    'action' => 'index',
                ]);
            }

            $this->Flash->error(
                'There was an error processing your registration. ' .
                'Please check for error messages and try again.'
            );
        } else {
            $this->Flash->error('CAPTCHA challenge failed. Please try again.');
        }

        $this->request = $this->request->withData('password', '');
        $this->request = $this->request->withData('confirm_password', '');

        return null;
    }

    /**
     * Method for /users/login
     *
     * @return Response|null
     */
    public function login()
    {
        $this->set('pageTitle', 'Log in');

        if (!$this->request->is('post')) {
            $user = $this->Users->newEntity();
            $user->auto_login = true;
            $this->set('user', $user);

            return null;
        }

        $user = $this->Auth->identify();
        if (!$user) {
            $this->Flash->error('Email or password is incorrect');
            $this->request = $this->request->withData('password', '');
            $this->set('user', $user);

            return null;
        }

        $this->Auth->setUser($user);

        // Remember login information
        if ($this->request->getData('auto_login')) {
            $this->response = $this->response->withCookie('CookieAuth', [
                'value' => [
                    'email' => $this->request->getData('email'),
                    'password' => $this->request->getData('password'),
                ],
                'secure' => true,
                'expire' => strtotime('+1 year'),
                'httpOnly' => true,
            ]);
        }

        return $this->redirect($this->Auth->redirectUrl());
    }

    /**
     * Method for /users/logout
     *
     * @return Response|null
     */
    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    /**
     * Page for displaying or generating an API key
     *
     * @return void
     */
    public function apiKey()
    {
        /** @var UsersTable $usersTable */
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $userId = $this->Auth->user('id');
        $apiKey = $usersTable->getApiKey($userId);

        if ($this->request->is('post')) {
            if ($apiKey) {
                $this->Flash->error('You already have an API key for your account');
            } elseif ($usersTable->setApiKey($userId)) {
                $this->Flash->success('API key generated');
                $apiKey = $usersTable->getApiKey($userId);
            } else {
                $this->Flash->error('There was an error generating your API key');
            }
        }

        $this->set([
            'apiKey' => $apiKey,
            'pageTitle' => $apiKey ? 'Your API Key' : 'Generate API Key',
        ]);
    }

    /**
     * Allows the user to enter their email address and get a link to reset their password
     *
     * @return void
     */
    public function forgotPassword()
    {
        $this->set([
            'pageTitle' => 'Forgot Password',
        ]);
    }

    /**
     * User's /account page
     *
     * @return void
     */
    public function account()
    {
        $userId = $this->Auth->user('id');
        $user = $this->Users->get($userId);
        if (!$this->request->is('get')) {
            $this->Users->patchEntity($user, $this->request->getData(), ['fields' => ['name', 'email']]);
            if ($this->Users->save($user)) {
                $this->Flash->success('Information updated.');
            } else {
                $this->Flash->error(
                    'Sorry, there was an error updating your information. ' .
                    'Check for error messages below, try again, and contact an administrator if you need assistance.'
                );
            }
        }

        $this->set([
            'pageTitle' => 'My Account',
            'hasSubscription' => (bool)$user->mailing_list_id,
            'user' => $user,
        ]);
    }

    /**
     * Page for changing one's own account password
     *
     * @return null
     */
    public function changePass()
    {
        $userId = $this->Auth->user('id');
        $user = $this->Users->get($userId);
        $this->set('pageTitle', 'Change Password');

        if ($this->request->is('get')) {
            $this->set('user', $user);

            return null;
        }

        $this->Users->patchEntity($user, $this->request->getData(), ['fields' => ['password', 'confirm_password']]);
        if ($this->Users->save($user)) {
            $this->Flash->success('Password changed.');
        } else {
            $this->Flash->error(
                'Sorry, there was an error changing your password. ' .
                'Check for error messages below, try again, and contact an administrator if you need assistance.'
            );
        }

        $this->set('user', $user);

        return null;
    }
}
