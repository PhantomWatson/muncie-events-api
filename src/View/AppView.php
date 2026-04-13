<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use App\View\Helper\CalendarHelper;
use App\View\Helper\IconHelper;
use App\View\Helper\NavHelper;
use App\View\Helper\TagHelper;
use Cake\View\View;
use Recaptcha\View\Helper\RecaptchaHelper;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 * @property \App\View\Helper\CalendarHelper $Calendar
 * @property \App\View\Helper\IconHelper $Icon
 * @property \App\View\Helper\NavHelper $Nav
 * @property \Recaptcha\View\Helper\RecaptchaHelper $Recaptcha
 * @property \App\View\Helper\TagHelper $Tag
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->loadHelper('Calendar');
        $this->loadHelper('Form', ['templates' => 'bootstrap_form']);
        $this->loadHelper('Nav');
        $this->loadHelper('Html');

        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');
        if ($controller == 'Events' && in_array($action, ['add', 'edit'])) {
            $this->loadHelper('Tag');
        }
    }
}
