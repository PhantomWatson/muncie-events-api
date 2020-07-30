<?php
namespace App\Controller;

use App\Widget\Widget;
use Cake\Routing\Router;
use Cake\Utility\Hash;

/**
 * Widgets Controller
 *
 * @property \App\Widget\Widget $Widgets
 * @property \App\Model\Table\EventsTable $Events
 */
class WidgetsController extends AppController
{
    /**
     * Initialization hook method
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow(['index']);
        $this->loadModel('Events');
        $this->Widget = new Widget();
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set([
            'pageTitle' => 'Website Widgets',
            'hideSidebar' => true,
        ]);
    }

    /**
     * Produces a view that lists seven event-populated days, starting with $startDate
     *
     * @param string $startDate 'yyyy-mm-dd', today by default
     * @return void
     */
    public function feed($startDate = null)
    {
        $this->setDemoData('feed');
        $options = $this->request->getQueryParams();
        $filters = $this->Widget->getEventFilters($options);
        $filters = $this->Widget->processTagFilters($filters);
        $this->Widget->processCustomStyles($options);
        $events = $this->Events
            ->find('forWidget', compact('startDate', 'filters'))
            ->all();

        $this->set([
            'pageTitle' => 'Upcoming Events',
            'events' => $events,
            'eventIds' => Hash::extract($events->toArray(), '{n}.id'),
            'isAjax' => $this->request->is('ajax'),
            'customStyles' => $this->Widget->getStyles(),
            'filters' => $filters,
            'categories' => $this->Events->Categories->find()->all(),
            'allEventsUrl' => $this->getAllEventsUrl(),
        ]);
        $this->viewbuilder()->setLayout($this->request->is('ajax') ? 'ajax' : 'Widgets' . DS . 'feed');
    }

    /**
     * Sends data to the view for the demonstration widget
     *
     * @param string $widgetType Either 'feed' or 'month
     * @return void
     */
    private function setDemoData($widgetType)
    {
        $this->Widget->setType($widgetType);
        $iframeQueryString = $this->Widget->getIframeQueryString();
        $options = $this->getOptions();
        $iframeStyles = $this->Widget->getIframeStyles($options);
        $this->set([
            'defaults' => $this->Widget->getDefaults(),
            'iframeStyles' => $iframeStyles,
            'iframeUrl' => Router::url([
                'controller' => 'widgets',
                'action' => $widgetType,
                '?' => $iframeQueryString,
            ], true),
            'codeUrl' => Router::url([
                'controller' => 'widgets',
                'action' => $widgetType,
                '?' => str_replace('&', '&amp;', $iframeQueryString),
            ], true),
            'categories' => $this->Events->Categories
                ->find()
                ->orderAsc('id')
                ->all(),
        ]);
    }

    /**
     * Returns an array of the valid and non-default widget options found in the query string
     *
     * @return array
     */
    public function getOptions()
    {
        $queryParams = $this->request->getQueryParams();
        if (!$queryParams) {
            return [];
        }

        $options = [];
        foreach ($queryParams as $key => $val) {
            // Clean up option and skip blanks
            $val = trim($val);
            if ($val == '') {
                continue;
            }
            $key = str_replace('amp;', '', $key);

            // Retain only valid options that differ from their default values
            if ($this->Widget->isValidNondefaultOption($key, $val)) {
                $options[$key] = $val;
            }
        }

        return $options;
    }

    /**
     * Returns the URL to view this calendar with no event filters (but custom styles retained)
     *
     * @return string
     */
    private function getAllEventsUrl()
    {
        $queryStringParams = $this->request->getQueryParams();
        $filteredParams = [];
        if ($queryStringParams) {
            $defaults = $this->Widget->getDefaults();
            foreach ($queryStringParams as $var => $val) {
                // Skip if this parameter is a type of event filter
                if (isset($defaults['event_options'][$var])) {
                    continue;
                }

                $filteredParams[$var] = $val;
            }
        }

        return Router::url([
            'controller' => 'Widgets',
            'action' => $this->request->getParam('action'),
            '?' => $filteredParams,
        ]);
    }

    /**
     * Displays an event's details
     *
     * @param int $eventId Event ID
     * @return void
     */
    public function event($eventId)
    {
        $event = $this->Events
            ->find('withAllAssociated')
            ->where(['Events.id' => $eventId])
            ->first();

        // Note: Both the 'feed' and 'month' widgets display their event details with the 'feed' layout
        $this->viewbuilder()->setLayout($this->request->is('ajax') ? 'ajax' : 'Widgets' . DS . 'feed');
        $this->set(compact('event'));
    }
}
