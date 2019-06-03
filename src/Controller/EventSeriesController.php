<?php
namespace App\Controller;

use App\Model\Entity\EventSeries;
use App\Model\Table\EventSeriesTable;
use App\Model\Table\EventsTable;
use Cake\Http\Response;
use Exception;

/**
 * EventSeries Controller
 *
 * @property EventSeriesTable $EventSeries
 * @property EventsTable $Events
 */
class EventSeriesController extends AppController
{
    public $helpers = ['Html'];

    /**
     * Initialization hook method.
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['view']);
    }

    /**
     * View method
     *
     * @param string|null $seriesId EventSeries id
     * @return Response|null
     */
    public function view($seriesId = null)
    {
        /** @var EventSeries $eventSeries */
        $eventSeries = $this->EventSeries
            ->find('forView')
            ->where(['EventSeries.id' => $seriesId])
            ->first();

        if ($eventSeries == null) {
            $this->Flash->error(__('Sorry, we can\'t find that event series.'));

            return $this->redirect(['controller' => 'events', 'action' => 'index']);
        }

        $eventSeries->splitEventsPastFuture();
        $dividedEvents = [];
        if ($eventSeries->pastEvents) {
            $dividedEvents['past'] = $eventSeries->pastEvents;
        }
        if ($eventSeries->upcomingEvents) {
            $dividedEvents['upcoming'] = $eventSeries->upcomingEvents;
        }

        $canEdit = (
            $this->Auth->user('role') == 'admin'
            || $this->Auth->user('id') == $eventSeries->user_id
        );

        $this->set([
            'canEdit' => $canEdit,
            'dividedEvents' => $dividedEvents,
            'eventSeries' => $eventSeries,
            'pageTitle' => 'Event Series: ' . $eventSeries->title
        ]);

        return null;
    }
}
