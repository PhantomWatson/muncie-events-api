<?php
namespace App\View\Helper;

use App\Model\Table\EventsTable;
use Cake\ORM\TableRegistry;
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

    /**
     * Returns a formatted array of all populated dates
     *
     * @return array
     */
    public function getPopulatedDates()
    {
        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $results = $eventsTable->getPopulatedDates();
        $populated = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result);
            $populated["$month-$year"][] = $day;
        }

        return $populated;
    }
}
