<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use App\Event\VEvent;
use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar([
    'VEVENT' => VEvent::getVevent($event),
]);

echo $vcalendar->serialize();
