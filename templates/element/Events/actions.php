<?php
/**
 * @var AppView $this
 * @var Event $event
 * @var array $authUser
 */

use App\Model\Entity\Event;
use App\View\AppView;
use App\View\Helper\CalendarHelper;

$isAdmin = isset($authUser['role']) && $authUser['role'] == 'admin';
$isAuthor = $event->user_id && ($authUser['id'] ?? null == $event->user_id);
$isApproved = (bool)$event->approved_by;
$canEdit = $isAdmin || $isAuthor;
$widget = $widget ?? false;
$dropdownMenuClasses = 'dropdown-menu';
if ($widget) {
    $dropdownMenuClasses .= ' dropdown-menu-right';
}
?>
<div class="actions">
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                title="Export to another calendar">
            <i class="fas fa-cloud-download-alt"></i>
            Export
        </button>
        <div class="<?= $dropdownMenuClasses ?>" aria-labelledby="dropdownMenuButton">
            <a href="<?= CalendarHelper::getGoogleCalendarUrl($event) ?>" title="Add to Google Calendar"
               class="dropdown-item">
                Google
            </a>
            <?= $this->Html->link(
                'Outlook / iCalendar (.ics)',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'view',
                    'id' => $event->id,
                    '_ext' => 'ics',

                ],
                [
                    'title' => 'Download iCalendar (.ics) file to import into Microsoft Outlook or another calendar application',
                    'class' => 'dropdown-item',
                ]
            ) ?>
        </div>
    </div>
    <?php if ($canEdit): ?>
        <div class="dropdown">
            <button
                class="btn btn-secondary dropdown-toggle btn-sm"
                type="button"
                id="manageEventDropdown"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                title="Manage this event"
            >
                Manage
            </button>
            <div class="<?= $dropdownMenuClasses ?>" aria-labelledby="manageEventDropdown">
                <?php if ($isAdmin && !$isApproved): ?>
                    <?= $this->Html->link(
                        'Approve',
                        [
                            'plugin' => false,
                            'prefix' => false,
                            'controller' => 'Events',
                            'action' => 'approve',
                            'id' => $event->id,
                        ],
                        [
                            'escape' => false,
                            'class' => 'dropdown-item',
                        ]
                    ) ?>
                <?php endif; ?>
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
                        'class' => 'dropdown-item',
                        'escape' => false,
                    ]
                ) ?>
                <?= $this->Html->link(
                    'Duplicate',
                    [
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'Events',
                        'action' => 'duplicate',
                        'id' => $event->id,
                    ],
                    [
                        'class' => 'dropdown-item',
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
                        'class' => 'dropdown-item',
                        'confirm' => 'Are you sure you want to delete this event?',
                        'escape' => false,
                    ]
                ) ?>
            </div>
        </div>
    <?php endif; ?>
</div>
