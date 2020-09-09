<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Validator\ContactValidator;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Mailer\Email;
use Exception;
use Recaptcha\Controller\Component\RecaptchaComponent;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 * @property RecaptchaComponent $Recaptcha
 */
class PagesController extends AppController
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

        $this->Auth->allow();

        if ($this->request->getParam('action') === 'contact') {
            $this->loadRecaptcha();
        }

        return null;
    }

    /**
     * Api information page
     *
     * @return void
     */
    public function api()
    {
        $this->set(['pageTitle' => 'Muncie Events API']);
    }

    /**
     * Docs page
     *
     * @return void
     */
    public function apiDocsV1()
    {
        $this->viewBuilder()->setLayout('api');
    }

    /**
     * Contact page
     *
     * @return null
     */
    public function contact()
    {
        $this->set('pageTitle', 'Contact Us');
        if (!$this->request->is('post')) {
            return null;
        }

        $validator = new ContactValidator();
        $data = $this->request->getData();
        $errors = $validator->errors($data);
        $authorized = $this->Recaptcha->verify();
        if (!empty($errors) || !$authorized) {
            $this->Flash->error('Message could not be sent. Please check for error messages and try again');

            return null;
        }

        $email = new Email('contact_form');
        $adminEmail = Configure::read('adminEmail');
        $email
            ->setFrom($data['email'], $data['name'])
            ->setTo($adminEmail)
            ->setSubject('Muncie Events contact form: ' . $data['category']);
        $isSent = $email->send($data['body']);
        if ($isSent) {
            $this->Flash->success('Thank you for contacting us. We will respond to your message as soon as we can.');

            // Clear form
            foreach (['body', 'email', 'name'] as $field) {
                $this->request = $this->request->withData($field, '');
            }

            return null;
        }

        $msg = sprintf(
            'There was a problem sending your message. Please contact an administrator at ' .
            '<a href="mailto:%s">%s</a> for assistance.',
            $adminEmail,
            $adminEmail
        );
        $this->Flash->error($msg);

        return null;
    }

    /**
     * About page
     *
     * @return void
     */
    public function about()
    {
        $this->set([
            'pageTitle' => 'About',
        ]);
    }

    /**
     * Terms of service page
     *
     * @return void
     */
    public function terms()
    {
        $this->set([
            'pageTitle' => 'Web Site Terms and Conditions of Use',
        ]);
    }
}
