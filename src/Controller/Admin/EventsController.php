<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Model\Table\EventSeriesTable;
use Cake\Http\Response;
use Exception;

/**
 * Events Controller
 *
 * @property EventSeriesTable $EventSeries
 */
class EventsController extends AppController
{
    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->Auth->deny();
    }

    /**
     * Returns TRUE if the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        return $user['role'] == 'admin';
    }

    /**
     * Displays a page of events waiting for moderator approval
     *
     * @return void
     */
    public function moderate()
    {
        $events = $this->Events->find('forModeration')->toArray();

        /* Find sets of identical events (belonging to the same series and with the same modified date)
         * and remove all but the first */
        $identicalSeries = [];
        foreach ($events as $k => $event) {
            if (!$event->series_id) {
                continue;
            }
            $eventId = $event->id;
            $seriesId = $event->series_id;
            $modified = date('Y-m-d', strtotime($event->modified));
            if (isset($identicalSeries[$seriesId][$modified])) {
                unset($events[$k]);
            }
            $identicalSeries[$seriesId][$modified][] = $eventId;
        }

        $this->set([
            'events' => $events,
            'identicalSeries' => $identicalSeries,
            'pageTitle' => 'Review Unapproved Events'
        ]);
    }

    /**
     * Marks the specified event as approved by an administrator
     *
     * @return Response
     */
    public function approve()
    {
        $eventIds = $this->request->getParam('pass');
        if (empty($eventIds)) {
            $this->Flash->error('No events approved because no IDs were specified');

            return $this->redirect('/');
        }

        $seriesToApprove = [];
        foreach ($eventIds as $id) {
            if (!$this->Events->exists($id)) {
                $this->Flash->error("Cannot approve. Event with ID# $id not found.");
                continue;
            }
            $event = $this->Events->get($id, [
                'contain' => 'EventSeries'
            ]);
            if ($event->series_id) {
                $seriesToApprove[$event->series_id] = true;
            }

            // Approve & publish it
            $event->approved_by = $this->Auth->user('id');
            $event->published = true;

            if ($this->Events->save($event)) {
                $this->Flash->success("Event #$id approved.");
            }
        }

        if ($seriesToApprove) {
            $this->loadModel('EventSeries');
            foreach ($seriesToApprove as $seriesId => $flag) {
                $series = $this->EventSeries->get($seriesId);
                $series->published = true;
                if ($this->EventSeries->save($series)) {
                    $this->Flash->success("Event series #$seriesId approved.");
                }
            }
        }

        return $this->redirect($this->referer());
    }
}
