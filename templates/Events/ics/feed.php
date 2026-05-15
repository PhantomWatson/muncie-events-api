<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[]|\Cake\Collection\CollectionInterface $events
 */

use App\Event\VEvent;
use App\Model\Entity\Event;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar();

// Determine the end of the range of dates needed for the VTIMEZONE definition
$to = new DateTime();
foreach ($events as $event) {
    $dateTime = new DateTime($event->date->format('Y-m-d'));
    if ($dateTime > $to) {
        $to = $dateTime;
    }
}
$to->setTime(23, 59, 59);

$vcalendar = Event::addVtimezone($vcalendar, Event::TIMEZONE, 0, $to->getTimestamp());

foreach ($events as $event) {
    $vcalendar->add('VEVENT', VEvent::getVevent($event));
}

echo $vcalendar->serialize();
