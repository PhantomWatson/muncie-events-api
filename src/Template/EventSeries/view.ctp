<?php
/**
 * @var AppView $this
 * @var array $dividedEvents
 * @var bool $canEdit
 * @var Event $event
 * @var EventSeries $eventSeries
 * @var string $pageTitle
 */
use App\Model\Entity\Event;
use App\Model\Entity\EventSeries;
use App\View\AppView; ?>

<h1 class="page_title">
    <?= $pageTitle ?>
</h1>

<div class="event_series">
    <?php if ($canEdit) : ?>
        <div class="controls">
            <?= $this->Html->link(
                'Edit',
                [
                    'controller' => 'EventSeries',
                    'action' => 'edit',
                    'id' => $eventSeries->id,
                ],
                [
                    'class' => 'btn btn-sm btn-secondary',
                    'escape' => false,
                ]
            ) ?>

            <?= $this->Form->postLink(
                'Delete',
                [
                    'controller' => 'EventSeries',
                    'action' => 'delete',
                    'id' => $eventSeries->id,
                ],
                [
                    'class' => 'btn btn-sm btn-secondary',
                    'escape' => false,
                    'confirm' => 'Delete all of the events in this series?',
                ]
            ) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($dividedEvents as $section => $events) : ?>
        <h2>
            <?= ucwords($section) ?> Events
        </h2>
        <table>
            <tbody>
                <?php foreach ($events as $key => $event) : ?>
                    <tr>
                        <td class="date">
                            <?= $event->date->format('M j, Y') ?>
                        </td>
                        <td class="time">
                            <?= $this->Calendar->time($event) ?>
                        </td>
                        <td>
                            <?= $this->Html->link(
                                $event->title,
                                [
                                    'plugin' => false,
                                    'prefix' => false,
                                    'controller' => 'Events',
                                    'action' => 'view',
                                    'id' => $event->id,
                                ]
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <p class="author">
        <?php if (isset($eventSeries->user->name)) : ?>
            Author:
            <?= $this->Html->link(
                $eventSeries->user->name,
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'view',
                    'id' => $eventSeries->user->id,
                ]
            ) ?>
        <?php else : ?>
            This event series was posted anonymously.
        <?php endif; ?>
    </p>
</div>
