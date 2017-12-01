<?php
namespace App\Controller;

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
    /**
     * Initialize method
     *
     * @return \Cake\Http\Response|null
     */
    public function initialize()
    {
        parent::initialize();

        if (!$this->request->is('ssl')) {
            return $this->redirect('https://' . env('SERVER_NAME') . $this->request->getRequestTarget());
        }

        $this->loadComponent('Recaptcha.Recaptcha', [
            'enable' => (bool)env('RECAPTCHA_ENABLED', true),
            'sitekey' => '6LeDpjoUAAAAADAE8vX2DOVuuRYQSmSqRhvxIr5G',
            'secret' => env('RECAPTCHA_SECRET'),
            'type' => 'image',
            'theme' => 'light',
            'lang' => 'en',
            'size' => 'normal'
        ]);

        $this->Auth->allow([
            'register', 'login', 'logout'
        ]);

        return null;
    }

    /**
     * Register page
     *
     * @return \Cake\Http\Response|null
     */
    public function register()
    {
        $user = $this->Users->newEntity();

        $this->set('user', $user);

        if ($this->request->is('post')) {
            if ($this->Recaptcha->verify()) {
                $user = $this->Users->patchEntity($user, $this->request->getData(), [
                    'fieldList' => ['name', 'email', 'password']
                ]);
                $user->role = 'user';

                if ($this->Users->save($user)) {
                    $this->Flash->success('Registration successful');
                    $this->Auth->setUser($user);

                    return $this->redirect([
                        'controller' => 'Pages',
                        'action' => 'home'
                    ]);
                }

                $msg =
                    'There was an error processing your registration. ' .
                    'Please check for error messages and try again.';
                $this->Flash->error($msg);
            } else {
                $this->Flash->error('CAPTCHA challenge failed. Please try again.');
            }

            $this->request = $this->request->withData('password', '');
            $this->request = $this->request->withData('confirm_password', '');
        }

        return null;
    }

    /**
     * Method for /users/login
     *
     * @return \Cake\Http\Response|null
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
                'httpOnly' => true
            ]);
        }

        return $this->redirect($this->Auth->redirectUrl());
    }

    /**
     * Method for /users/logout
     *
     * @return \Cake\Http\Response|null
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
        $usersTable = TableRegistry::get('Users');
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
            'pageTitle' => $apiKey ? 'Your API Key' : 'Generate API Key'
        ]);
    }
}
