<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

use App\Event\VEvent;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar();
foreach ($events as $event) {
    $vcalendar->add('VEVENT', VEvent::getVevent($event));
}

echo $vcalendar->serialize();
