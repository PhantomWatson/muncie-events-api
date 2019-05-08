<?php
/**
 * @var AppView $this
 * @var Event[] $events
 */
use App\Model\Entity\Event;
use App\View\AppView;
?>
<div id="calendar_list_view_wrapper">
    <div class="event_accordion" id="event_accordion">
        <?php if (empty($events)): ?>
            <p class="no_events alert alert-info" id="no_events">
                No upcoming events found.
            </p>
        <?php else: ?>
            <?= $this->element('Events/accordion/index', ['events' => $events]) ?>
        <?php endif; ?>
    </div>
</div>
