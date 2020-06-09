<?php
namespace App\View\Helper;

use App\Model\Entity\Tag;
use App\Model\Table\EventsTable;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\View\Helper;

/**
 * Class NavHelper
 *
 * Used to help with displaying and passing variables to elements in the header and sidebar
 *
 * @package App\View\Helper
 */
class NavHelper extends Helper
{
    /**
     * Takes a controller and action name and returns either 'active' or null if they correspond to the current request
     *
     * @param string $controller Name of a controller to compare to the current request
     * @param string $action Name of an action to compare to the current request
     * @param string $pass Passed parameter
     * @return string|null
     */
    public function getActiveLink($controller, $action, $pass = null)
    {
        if ($this->_View->getRequest()->getParam('controller') != $controller) {
            return null;
        }
        if ($this->_View->getRequest()->getParam('action') != $action) {
            return null;
        }
        if ($pass) {
            if ($this->_View->getRequest()->getParam('pass')[0] != $pass) {
                return null;
            }
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
                        'action' => 'today',
                    ]),
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
                        'action' => 'tomorrow',
                    ]),
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
                    $year,
                ]),
            ];
            if (count($dayLinks) == $limit) {
                break;
            }
        }

        return $dayLinks;
    }

    /**
     * Returns an array of location slugs => names
     *
     * @return array
     */
    public function getLocations()
    {
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $locations = $eventsTable
            ->find('future')
            ->select(['location', 'location_slug']);

        if (!$locations->count()) {
            return [];
        }

        $slugs = [];
        $locs = [];
        foreach ($locations as $location) {
            $locs[] = $location->location;
            $slugs[] = $location->location_slug;
        }
        $retval = array_combine($locs, $slugs);
        ksort($retval);

        return $retval;
    }

    /**
     * Returns an array of tags associated with upcoming events with the 'count' property set
     *
     * @return Tag[]
     */
    public function getUpcomingTags()
    {
        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');

        return $eventsTable->getEventTags();
    }

    /**
     * Returns an array of categories and extra information used to display them in the sidebar
     *
     * @return array
     */
    public function getCategories()
    {
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $categories = $categoriesTable
            ->find()
            ->orderAsc('weight')
            ->toArray();

        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $counts = [];
        foreach ($categories as $category) {
            $counts[$category->id] = $eventsTable->getCategoryUpcomingEventCount($category->id);
        }

        foreach ($categories as $category) {
            $category->count = $counts[$category->id];

            $category->upcomingEventsTitle = sprintf(
                '%s upcoming %s',
                $category->count,
                __n('event', 'events', $category->count)
            );
        }

        return $categories;
    }
}
