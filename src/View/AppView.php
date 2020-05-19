<?php
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

use AkkaCKEditor\View\Helper\CKEditorHelper;
use App\View\Helper\CalendarHelper;
use App\View\Helper\IconHelper;
use App\View\Helper\NavHelper;
use App\View\Helper\TagHelper;
use Cake\View\View;
use Recaptcha\View\Helper\RecaptchaHelper;

/**
 * Application View
 *
 * Your application’s default view class
 *
 * @link https://book.cakephp.org/3.0/en/views.html#the-app-view
 * @property CalendarHelper $Calendar
 * @property CKEditorHelper $CKEditor
 * @property IconHelper $Icon
 * @property NavHelper $Nav
 * @property RecaptchaHelper $Recaptcha
 * @property TagHelper $Tag
 */
class AppView extends View
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading helpers.
     *
     * e.g. `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize()
    {
        $this->loadHelper('Calendar');
        $this->loadHelper('Form', ['templates' => 'bootstrap_form']);
        $this->loadHelper('Nav');

        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');
        if ($controller == 'Events' && in_array($action, ['add', 'edit'])) {
            $this->loadHelper('Tag');
            $this->loadHelper('AkkaCKEditor.CKEditor', [
                'distribution' => 'basic',
                'version' => '4.5.0',
            ]);
        }
    }
}
