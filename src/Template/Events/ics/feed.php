<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

use App\Event\VEvent;
use App\Model\Entity\Event;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar();
$from = 0; // now

// Determine the end of the range of dates needed for the VTIMEZONE definition
$to = 0;
foreach ($events as $event) {
    $timestamp = $event->date->setTime(23, 59, 59)->toUnixString();
    if ($timestamp > $to) {
        $to = $timestamp;
    }
}

$vcalendar = Event::addVtimezone($vcalendar, Event::TIMEZONE, $from, $to);

foreach ($events as $event) {
    $vcalendar->add('VEVENT', VEvent::getVevent($event));
}

echo $vcalendar->serialize();
