<?php
namespace App\Controller;

use App\Model\Table\UsersTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;
use Recaptcha\Controller\Component\RecaptchaComponent;

/**
 * Class UsersController
 * @package App\Controller
 * @property \App\Model\Table\UsersTable $Users
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
    public function initialize(): void
    {
        parent::initialize();

        $this->loadRecaptcha();

        $this->Auth->allow([
            'forgotPassword',
            'login',
            'logout',
            'register',
            'resetPassword',
            'view',
        ]);
    }

    /**
     * Register page
     *
     * @return Response|null
     */
    public function register()
    {
        $user = $this->Users->newEmptyEntity();

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
        $userEntity = $this->Users->newEmptyEntity();
        $userEntity->auto_login = true;
        $this->set([
            'pageTitle' => 'Log in',
            'user' => $userEntity,
        ]);

        if (!$this->request->is('post')) {
            return null;
        }

        $userData = $this->Auth->identify();
        if (!$userData) {
            $this->Flash->error('Email or password is incorrect');
            $this->request = $this->request->withData('password', '');

            return null;
        }

        $this->Auth->setUser($userData);

        // Remember login information
        if ($this->request->getData('auto_login')) {
            $cookie = (new Cookie('CookieAuth'))
                ->withValue([
                    'email' => $this->request->getData('email'),
                    'password' => $this->request->getData('password'),
                ])
                ->withSecure(true)
                ->withExpiry(new \DateTime('+1 year'))
                ->withHttpOnly(true);
            $this->response = $this->response->withCookie($cookie);
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

    /**
     * Page displaying a user's submitted events
     *
     * @param null $userId User ID
     * @return \Cake\Http\Response|null
     */
    public function view($userId = null)
    {
        try {
            $user = $this->Users->get($userId);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error(
                'Sorry, we couldn\'t find that user. ' .
                'You may have followed a link to a user profile that has been removed.'
            );

            return $this->redirect('/');
        }

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $query = $eventsTable
            ->find('published')
            ->find('ordered', direction: 'DESC')
            ->find('withAllAssociated')
            ->where(['Events.user_id' => $userId]);

        $events = $this->paginate($query);
        $totalCount = $query->count();

        $this->set([
            'events' => $events,
            'loggedIn' => (bool)$this->Auth->user(),
            'pageTitle' => $user->name,
            'totalCount' => $totalCount,
            'user' => $user,
        ]);

        return null;
    }

    /**
     * Resets the user's password
     *
     * @param int $userId User ID
     * @param string $resetPasswordHash A string to ensure that this URL can't be randomly guessed
     * @return null
     */
    public function resetPassword($userId, $resetPasswordHash)
    {
        $user = $this->Users->get($userId);
        $email = $user['email'];

        $this->set([
            'pageTitle' => 'Reset Password',
            'userId' => $userId,
            'email' => $email,
            'resetPasswordHash' => $resetPasswordHash,
            'user' => $user,
        ]);

        $expectedHash = $this->Users->getResetPasswordHash($userId, $email);

        if ($resetPasswordHash != $expectedHash) {
            $this->Flash->error('Invalid password-resetting code. Make sure that you entered the correct address and that the link emailed to you hasn\'t expired.');
            $this->redirect('/');
        }

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, [
                'password' => $this->request->getData('new_password'),
                'confirm_password' => $this->request->getData('new_confirm_password')
            ]);
            $user->password = $this->request->getData('new_password');

            if ($this->Users->save($user)) {
                $data = $user->toArray();
                $this->Auth->setUser($data);
                $this->Flash->success('Password changed. You are now logged in.');

                return null;
            }
            $this->Flash->error('There was an error changing your password. Please check to make sure they\'ve been entered correctly.');

            return $this->redirect('/');
        }

        return null;
    }
}
