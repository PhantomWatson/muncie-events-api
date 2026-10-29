<?php
/**
 * @var \App\View\AppView $this
 * @var Event[] $events
 * @var bool|null $sortNewestFirst
 */

use App\Model\Entity\Event;

$sortNewestFirst ??= false;
?>
<div id="calendar_list_view_wrapper">
    <div class="event_accordion" id="event_accordion">
        <?= $this->element('Events/accordion/index', compact('events', 'sortNewestFirst')) ?>
    </div>
</div>
