<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use Sabre\VObject\Component\VCalendar;

$vcalendar = new VCalendar([
    'VEVENT' => $this->element('Events/vevent'),
]);

echo $vcalendar->serialize();
