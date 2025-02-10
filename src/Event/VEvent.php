<?php
namespace App\Event;

use App\Model\Entity\Event;
use Cake\Routing\Router;

class VEvent
{
    /**
     * @param \App\Model\Entity\Event $event
     * @return array
     * @throws \Exception
     */
    public static function getVevent($event) {
        $tz = Event::TIMEZONE;
        $retval = [
            'CATEGORIES' => $event->category->name,
            'COMMENT' => $event->source ? "Info source: $event->source" : null,
            'CONTACT' => $event->user->email ?? null,
            "DTSTART;TZID=$tz" => $event->ical_time_start,
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
        ];

        if ($event->ical_time_end) {
            $retval["DTEND;TZID=$tz"] = $event->ical_time_end;
        }

        return $retval;
    }
}
