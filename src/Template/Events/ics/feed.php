<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

use App\Event\VEvent;
use App\Model\Entity\Event;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar();
$vcalendar = Event::addVtimezone($vcalendar, Event::TIMEZONE);

foreach ($events as $event) {
    $vcalendar->add('VEVENT', VEvent::getVevent($event));
}

echo $vcalendar->serialize();
