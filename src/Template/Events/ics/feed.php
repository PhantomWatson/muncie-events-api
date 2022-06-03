<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[] $events
 */

use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar();
foreach ($events as $event) {
    $vcalendar->add('VEVENT', $this->element('Events/vevent'));
}

echo $vcalendar->serialize();
