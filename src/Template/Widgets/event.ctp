<?php
/**
 * @var \App\Model\Entity\Event $event
 */

use Cake\Routing\Router;
use Cake\Utility\Hash;

$eventUrl = Router::url([
    'controller' => 'Events',
    'action' => 'view',
    'id' => $event->id,
], true);
$this->Html->scriptBlock('setupEventActions(".event");', ['block' => true])
?>

<div class="event">
    <h1 class="title">
        <?= $event->title ?>
    </h1>
    <?= $this->element('Events/actions', ['event' => $event]) ?>
    <div class="header_details">
        <table class="details">
            <tr>
                <th>When</th>
                <td>
                    <?= $event->date->format('l, F j, Y') ?>
                    <br />
                    <?= $this->Calendar->time($event) ?>
                </td>
            </tr>
            <tr>
                <th>Where</th>
                <td>
                    <?= $event->location ?>
                    <?= $event->location_details ? '<br />' . $event->location_details : '' ?>
                    <?= $event->address ? '<br />' . $event->address : '' ?>
                </td>
            </tr>
            <tr>
                <th>What</th>
                <td class="what">
                    <?php
                        echo $this->Icon->category($event->category->name) . $event->category->name;
                        if ($event->tags) {
                            echo sprintf(
                                ': <span class="tags">%s</div>',
                                implode(', ', Hash::extract($event->tags, '{n}.name'))
                            );
                        }
                    ?>
                </td>
            </tr>
            <?php if ($event->cost): ?>
                <tr>
                    <th>Cost</th>
                    <td><?= $event->cost ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($event->age_restriction): ?>
                <tr>
                    <th>Ages</th>
                    <td><?= $event->age_restriction ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($event->images): ?>
                <tr>
                    <th>Images</th>
                    <td>
                        <?php foreach ($event->images as $image): ?>
                            <?= $this->Calendar->thumbnail(
                                'tiny',
                                [
                                    'filename' => $image->filename,
                                    'caption' => $image->caption,
                                    'group' => 'event_view' . $event->id,
                                ]
                            ) ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="description">
        <?= $this->Text->autolink($event->description, ['escape' => false]) ?>
    </div>
    <div class="footer">
        <?= $this->Html->link('Go to event page', $eventUrl) ?>
        <?php if ($event->source): ?>
            <br />
            Source:
            <?= $this->Text->autoLink($event->source) ?>
        <?php endif; ?>
    </div>
</div>
