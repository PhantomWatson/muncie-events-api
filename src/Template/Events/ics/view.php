<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use App\Model\Entity\Event;
use Cake\Routing\Router;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar([
    'VEVENT' => [
        'CATEGORIES' => $event->category->name,
        'COMMENT' => $event->source ? "Info source: $event->source" : null,
        'CONTACT' => $event->user->email,
        'DTSTART' => Event::getDatetime($event->date, $event->time_start),
        'DTEND' => Event::getDatetime($event->date, $event->time_end),
        'DESCRIPTION' => $event->description_plaintext,
        'LOCATION' => sprintf(
            '%s%s%s',
            $event->location,
            $event->location_details ? ", $event->location_details" : null,
            $event->address ? " ($event->address)" : null
        ),
        'SUMMARY' => $event->title,
        'UID' => $event->id . '@muncieevents.com',
        'URL' => Router::url([
            'prefix' => false,
            'controller' => 'Events',
            'action' => 'view',
            'id' => $event->id,
        ], true),
    ],
]);

echo $vcalendar->serialize();
