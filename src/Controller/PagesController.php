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
    public function initialize(): void
    {
        parent::initialize();

        $this->Auth->allow();

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
     * @return void
     */
    public function contact()
    {
        $this->set('pageTitle', 'Contact Us');
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

    /**
     * Used for automated attack vectors
     *
     * @return void
     */
    public function blackhole()
    {
        exit;
    }
}
