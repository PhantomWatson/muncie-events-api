<?php
namespace App\Controller;

use App\Model\Entity\EventSeries;
use App\Model\Entity\User;
use App\Model\Table\EventSeriesTable;
use App\Model\Table\EventsTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
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
    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow(['view']);
    }

    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        // Grant access if this user is an admin
        $role = $user['role'] ?? null;
        if ($role == 'admin') {
            return true;
        }

        // Grant access to edit and delete functions only if the current user is this series's author
        $seriesId = $this->request->getParam('id');
        $userId = php_sapi_name() == 'cli'
            ? $this->request->getSession()->read('Auth.User.id')
            : $user['id'];

        return $this->EventSeries->exists([
            'id' => $seriesId,
            'user_id' => $userId,
        ]);
    }

    /**
     * Edit method
     *
     * @param string|null $seriesId Event series ID
     * @return Response|null
     * @throws NotFoundException
     */
    public function edit($seriesId = null)
    {
        try {
            /** @var EventSeries $eventSeries */
            $eventSeries = $this->EventSeries
                ->find('forEdit')
                ->where(['id' => $seriesId])
                ->first();
        } catch (RecordNotFoundException $exception) {
            $this->Flash->error('Sorry, we can\'t find that event series.');

            return $this->redirect('/');
        }

        if ($this->request->is(['post'])) {
            $x = 0;
            foreach ($this->request->getData('events') as $event) {
                // Skip
                if ($event['edited'] != 1) {
                    $x = $x + 1;
                    continue;
                }

                if ($event['delete'] == 1) {
                    if ($this->Events->delete($eventSeries->events[$x])) {
                        $this->Flash->success('Event deleted: ' . $event['id'] . '.');
                    }
                    $x = $x + 1;
                    continue;
                }

                $eventSeries->events[$x] = $this->Events->get($event['id']);
                $timeString = sprintf(
                    '%s:%s %s',
                    $event['time_start']['hour'],
                    $event['time_start']['minute'],
                    $event['time_start']['meridian']
                );
                $eventSeries->events[$x]->time_start = new \Cake\I18n\DateTime(
                    date('H:i', strtotime($timeString))
                );
                $eventSeries->events[$x]->title = $event['title'] ?: $eventSeries->events[$x]->title;

                if ($this->Events->save($eventSeries->events[$x])) {
                    $this->Flash->success('Event #' . $event['id'] . ' has been saved.');
                    $x = $x + 1;
                    continue;
                }

                $this->Flash->error('Event #' . $event['id'] . ' was not saved.');
                $x = $x + 1;
            }

            $eventSeries->title = $this->request->getData('title');
            if ($this->EventSeries->save($eventSeries)) {
                $this->Flash->success('The event series has been saved.');

                return $this->redirect(['action' => 'view', $seriesId]);
            }

            $this->Flash->error('The event series has NOT been saved.');
        }

        $this->set([
            'eventSeries' => $eventSeries,
            'pageTitle' => 'Edit Series: ' . $eventSeries->title,
        ]);

        return null;
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

        $eventSeries->splitEventsPastUpcoming();
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
            'pageTitle' => 'Event Series: ' . $eventSeries->title,
        ]);

        return null;
    }

    /**
     * Deletes the specified event series
     *
     * @param int $seriesId EventSeries ID
     * @return Response
     */
    public function delete($seriesId)
    {
        $eventSeries = $this->EventSeries->get($seriesId);

        if ($this->EventSeries->delete($eventSeries)) {
            $this->Flash->success('The event series has been deleted');

            return $this->redirect('/');
        }

        $this->Flash->error(
            'There was an error deleting that event series. ' .
            'Please try again or contact an administrator for assistance.'
        );

        return $this->redirect($this->referer());
    }
}
