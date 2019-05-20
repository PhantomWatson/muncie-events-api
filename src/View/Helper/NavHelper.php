<?php
namespace App\View\Helper;

use App\Model\Table\EventsTable;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
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

    /**
     * Returns a maximum of seven URLs for the soonest dates with upcoming events
     *
     * @return array
     */
    public function getDayLinks()
    {
        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $populatedDates = $eventsTable->getPopulatedDates();
        $dayLinks = [];
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('tomorrow'));
        $limit = 7;
        foreach ($populatedDates as $date) {
            if ($date == $today) {
                $dayLinks[] = [
                    'label' => 'Today',
                    'url' => Router::url([
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'today'
                    ])
                ];
                continue;
            }

            if ($date == $tomorrow) {
                $dayLinks[] = [
                    'label' => 'Tomorrow',
                    'url' => Router::url([
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'tomorrow'
                    ])
                ];
                continue;
            }
            list($year, $month, $day) = explode('-', $date);
            $dayLinks[] = [
                'label' => date('D, M j', strtotime($date)),
                'url' => Router::url([
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'day',
                    $month,
                    $day,
                    $year
                ])
            ];
            if (count($dayLinks) == $limit) {
                break;
            }
        }

        return $dayLinks;
    }
}
