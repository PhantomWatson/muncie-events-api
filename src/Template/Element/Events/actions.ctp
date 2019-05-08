<?php
/**
 * @var AppView $this
 * @var Event $event
 * @var array $authUser
 */

use App\Model\Entity\Event;
use App\View\AppView;
use App\View\Helper\CalendarHelper;

$isAdmin = $authUser['role'] == 'admin';
$isAuthor = $authUser['id'] == $event->user_id;
$isApproved = (bool)$event->approved_by;
$canEdit = $authUser['id'] && ($isAdmin || $isAuthor);
?>
<div class="actions">
    <div class="export_options_container">
        <a href="#<?= $event->id ?>_options" id="export_event_<?= $event->id ?>" class="export_options_toggler"
           data-toggle="collapse" data-target="#<?= $event->id ?>_options" aria-expanded="false"
           aria-controls="<?= $event->id ?>_options">
            <img src="/img/icons/calendar--arrow.png" alt="Export" title="Export to another calendar application" />
            Export
        </a>
        <div class="export_options collapse" id="<?= $event->id ?>_options">
            <?= $this->Html->link(
                'iCal',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'ics',
                    $event->id
                ],
                ['title' => 'Download iCalendar (.ICS) file']
            ) ?>
            <?= CalendarHelper::getGoogleCalendarLink($event) ?>
            <?= $this->Html->link(
                'Outlook',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'ics',
                    $event->id
                ],
                ['title' => 'Add to Microsoft Outlook']
            ) ?>
        </div>
    </div>
    <?php if ($isAdmin && !$isApproved): ?>
        <?= $this->Html->link(
            $this->Html->image('/img/icons/tick.png', ['alt' => 'Approve this event']) . 'Approve',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'approve',
                'id' => $event->id
            ],
            ['escape' => false]
        ) ?>
    <?php endif; ?>
    <?php if ($canEdit): ?>
        <?= $this->Html->link(
            $this->Html->image('/img/icons/pencil.png', ['alt' => 'Edit this event']) . 'Edit',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'edit',
                'id' => $event->id
            ],
            ['escape' => false]
        ) ?>
        <?= $this->Form->postLink(
            $this->Html->image('/img/icons/cross.png', ['alt' => 'Delete this event']) . 'Delete',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'delete',
                'id' => $event->id
            ],
            [
                'confirm' => 'Are you sure you want to delete this event?',
                'escape' => false
            ]
        ) ?>
    <?php endif; ?>
</div>
