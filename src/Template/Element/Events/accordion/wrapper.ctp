<?php
/**
 * @var AppView $this
 * @var Event[] $events
 */
use App\Model\Entity\Event;
use App\View\AppView;

$this->Html->scriptBlock('setupEventAccordion();', ['block' => true]);
?>
<div id="calendar_list_view_wrapper">
    <div class="event_accordion" id="event_accordion">
        <?= $this->element('Events/accordion/index', ['events' => $events]) ?>
    </div>
</div>
