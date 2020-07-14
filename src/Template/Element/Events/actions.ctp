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
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    title="Export to another calendar">
                <i class="fas fa-cloud-download-alt"></i>
                Export to...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a href="<?= CalendarHelper::getGoogleCalendarUrl($event) ?>" title="Add to Google Calendar"
                   class="dropdown-item">
                    Google
                </a>
                <?= $this->Html->link(
                    'Outlook',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'ics',
                        $event->id,
                    ],
                    [
                        'title' => 'Add to Microsoft Outlook',
                        'class' => 'dropdown-item',
                    ]
                ) ?>
                <?= $this->Html->link(
                    'iCalendar (.ics) file',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'ics',
                        $event->id,
                    ],
                    [
                        'title' => 'Download iCalendar (.ICS) file',
                        'class' => 'dropdown-item',
                    ]
                ) ?>
            </div>
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
            'Edit',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'edit',
                'id' => $event->id,
            ],
            [
                'class' => 'btn btn-sm btn-secondary',
                'escape' => false,
            ]
        ) ?>
        <?= $this->Form->postLink(
            'Delete',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'delete',
                'id' => $event->id,
            ],
            [
                'class' => 'btn btn-sm btn-secondary',
                'confirm' => 'Are you sure you want to delete this event?',
                'escape' => false,
            ]
        ) ?>
    <?php endif; ?>
</div>
