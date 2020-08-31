<?php

namespace App\Form;

use App\Model\Entity\Event;
use App\Model\Entity\User;
use App\Model\Table\EventsTable;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Exception;

/**
 * Event Form
 *
 * @property array $errors
 * @property EventsTable $Events
 */
class EventForm
{
    private $errors = [];
    private $Events;

    /**
     * EventForm constructor
     */
    public function __construct()
    {
        $this->Events = TableRegistry::getTableLocator()->get('Events');
    }

    /**
     * Processes request data and adds a single event (not connected to a series)
     *
     * @param array $data Request data
     * @param string $date A strtotime parsable date
     * @param array|null|User $user An array of user data, or null if user is anonymous
     * @return Event
     * @throws BadRequestException
     */
    public function addSingleEvent(array $data, $date, $user)
    {
        if (!is_string($date)) {
            throw new BadRequestException(sprintf(
                "Error: Dates must be passed as strings (%s provided)",
                gettype($date)
            ));
        }
        $data['date'] = $this->parseDate($date);
        foreach (['time_start', 'time_end'] as $timeField) {
            if (isset($data[$timeField]) && $data[$timeField] == '') {
                $data[$timeField] = null;
            }
            if (!isset($data[$timeField])) {
                continue;
            }
            $data[$timeField] = $this->parseTime($date, $data[$timeField]);
        }

        // Remove event series data, because it's saved separately
        unset($data['event_series']);

        $event = $this->Events->newEntity($data);
        $event->autoApprove($user);
        $event->autoPublish($user);
        $tagIds = $data['tags']['_ids'] ?? ($data['tag_ids'] ?? []);
        $tagNames = $data['customTags'] ?? ($data['tag_names'] ?? []);
        $event->processTags($tagIds, $tagNames);
        $event->setImageJoinData($data['images']);
        $event->category = $this->Events->Categories->get($event->category_id);
        $event->user_id = $user['id'] ?? null;
        $event->setLocationSlug();
        if ($event->user_id) {
            $usersTable = TableRegistry::getTableLocator()->get('Users');
            if ($usersTable->exists(['id' => $event->user_id])) {
                $event->user = $usersTable->get($event->user_id);
            } else {
                throw new BadRequestException("Invalid user ID: $event->user_id");
            }
        }

        $saved = $this->Events->save($event, [
            'associated' => ['Images', 'Tags'],
        ]);
        if (!$saved) {
            $this->errors = $event->getErrors();
            $msg = $this->getEventErrorMessage($event);
            throw new BadRequestException($msg);
        }

        return $saved;
    }

    /**
     * Takes an array of events and creates a series to associate them with
     *
     * @param Event[] $events An array of events in this series
     * @param string|null $seriesTitle Series title
     * @param User|null $user A user entity, or null if user is anonymous
     * @return Event[]
     * @throws BadRequestException
     */
    public function addEventSeries(array $events, $seriesTitle, $user)
    {
        // Create series
        $seriesTable = TableRegistry::getTableLocator()->get('EventSeries');
        $arbitraryEvent = $events[0];
        $series = $seriesTable->newEntity([
            'title' => $seriesTitle ? $seriesTitle : $arbitraryEvent->title,
            'user_id' => $arbitraryEvent->user_id,
            'published' => $arbitraryEvent->userIsAutoPublishable($user),
        ]);
        if (!$seriesTable->save($series)) {
            $adminEmail = Configure::read('adminEmail');
            $msg = 'The event could not be submitted. Please correct any errors and try again. If you need ' .
                'assistance, please contact an administrator at ' . $adminEmail . '.';
            throw new BadRequestException($msg);
        }

        // Associate events with the new series
        foreach ($events as &$event) {
            $this->Events->patchEntity($event, ['series_id' => $series->id]);
            $event->event_series = $series;
            if (!$this->Events->save($event)) {
                throw new InternalErrorException('Error associating event with series');
            }
        }

        return $events;
    }

    /**
     * Returns a FrozenTime object, throwing a BadRequestException if the provided time string can't be parsed
     *
     * @param FrozenDate|string $date Date object or string
     * @param array|string $time Time string (e.g. 2:30pm, 02:30 PM, 14:30) or hour/minute/meridian array
     * @return FrozenTime
     * @throws BadRequestException
     */
    public function parseTime($date, $time)
    {
        if (is_array($time)) {
            $keysExist = array_key_exists('hour', $time)
                && array_key_exists('minute', $time)
                && array_key_exists('meridian', $time);
            if (!$keysExist) {
                throw new BadRequestException(
                    'Time was provided as an array, but does not have required hour, minute, and meridian keys.'
                );
            }
        }

        try {
            if (is_array($time)) {
                $time = $time['hour'] . ':' . $time['minute'] . $time['meridian'];
            }

            return new FrozenTime($date . ' ' . $time, Event::TIMEZONE);
        } catch (Exception $e) {
            throw new BadRequestException(sprintf(
                'Invalid time: %s. Please provide this value in a format such as 2:30pm, 02:30 PM, 14:30, etc.',
                $time
            ));
        }
    }

    /**
     * Returns a FrozenDate object, throwing a BadRequestException if the provided date string can't be parsed
     *
     * @param string $date Date string in format YYYY-MM-DD
     * @return FrozenDate
     * @throws BadRequestException
     */
    public function parseDate($date)
    {
        try {
            return new FrozenDate($date);
        } catch (Exception $e) {
            throw new BadRequestException(sprintf(
                'Invalid date: %s. Please provide a date in the format YYYY-MM-DD.',
                $date
            ));
        }
    }

    /**
     * Returns a message to be output to the user for an event with one or more errors
     *
     * @param Event $event Event entity
     * @return string
     */
    public function getEventErrorMessage(Event $event)
    {
        $errors = $event->getErrors();
        if ($errors) {
            $msg = sprintf(
                'Please correct the following %s and try again. ',
                __n('error', 'errors', count($errors))
            );
            foreach ($errors as $field => $fieldErrors) {
                $field = ucwords(str_replace('_', ' ', $field));
                $fieldErrors = Hash::flatten($fieldErrors);
                $msg .= "$field: " . implode('; ', $fieldErrors) . '. ';
            }
        } else {
            $msg = 'There was an error submitting this event. ';
        }
        $msg .= sprintf(
            'If you need assistance, please contact an administrator at %s.',
            Configure::read('adminEmail')
        );

        return $msg;
    }

    /**
     * Returns the value of $this->errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
