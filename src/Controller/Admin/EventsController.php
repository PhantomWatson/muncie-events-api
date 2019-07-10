<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;
use Exception;

/**
 * Events Controller
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
}
