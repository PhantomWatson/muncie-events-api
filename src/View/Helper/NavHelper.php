<?php
namespace App\View\Helper;

use Cake\View\Helper;

class NavHelper extends Helper
{
    public function getActiveLink($controller, $action) {
        if ($this->_View->getRequest()->getParam('controller') != $controller) {
            return null;
        }
        if ($this->_View->getRequest()->getParam('action') != $action) {
            return null;
        }

        return 'active';
    }
}
