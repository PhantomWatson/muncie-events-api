<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

use App\Event\VEvent;
use App\Model\Entity\Event;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar();
$vtimezone = Event::generate_vtimezone(Event::TIMEZONE);
$vcalendar->add('VTIMEZONE', $vtimezone);

foreach ($events as $event) {
    $vcalendar->add('VEVENT', VEvent::getVevent($event));
}

echo $vcalendar->serialize();
