<?php
namespace App\Controller;

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

        if (! $this->request->is('ssl')) {
            return $this->redirect('https://' . env('SERVER_NAME') . $this->request->getRequestTarget());
        }

        $this->loadComponent('Recaptcha.Recaptcha', [
            'enable' => true,
            'sitekey' => '6LeDpjoUAAAAADAE8vX2DOVuuRYQSmSqRhvxIr5G',
            'secret' => env('RECAPTCHA_SECRET'),
            'type' => 'image',
            'theme' => 'light',
            'lang' => 'en',
            'size' => 'normal'
        ]);

        $this->Auth->allow([
            'register'
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
}
