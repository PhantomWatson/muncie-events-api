<?php
/**
 * @var AppView $this
 * @var Event $event
 * @var Image $image
 */

use App\Model\Entity\Event;
use App\Model\Entity\Image;
use App\View\AppView;
use App\View\Helper\CalendarHelper;
use App\View\Helper\IconHelper;
use Cake\Routing\Router;

$url = Router::url([
    'controller' => 'Events',
    'action' => 'view',
    'id' => $event->id,
], true);
$class = empty($event->images) ? '' : 'with_images';
?>

<li class="<?= $class ?>">
    <?php if (!empty($event->images)) : ?>
        <span class="tiny_thumbnails">
            <?php foreach ($event->images as $image) : ?>
                <?= CalendarHelper::thumbnail('tiny', [
                    'filename' => $image->filename,
                    'caption' => $image->caption,
                    'group' => 'event' . $event->id . '_tiny_tn',
                ]) ?>
            <?php endforeach; ?>
        </span>
    <?php endif; ?>
    <a data-toggle="collapse" data-target="#more_info_<?= $event->id ?>" href="<?= $url ?>" title="Click for more info"
       class="more_info_handle" id="more_info_handle_<?= $event->id ?>" data-event-id="<?= $event->id ?>">
        <?= IconHelper::category($event->category->name); ?>
        <span class="title">
            <?= $event->title ?>
        </span>

        <span class="when">
            <?= CalendarHelper::time($event) ?>
            @
        </span>

        <span class="where">
            <?= $event->location ?: '&nbsp;' ?>

            <?php if ($event->location_details) : ?>
                <span class="location_details">
                    <?= $event->location_details ?>
                </span>
            <?php endif; ?>

            <?php if ($event->address) : ?>
                <span class="address">
                     <?= $event->address ?>
                </span>
            <?php endif; ?>
        </span>
    </a>

    <div class="collapse" id="more_info_<?= $event->id ?>">
    <div class="card">
        <div class="card-header">
            <?= $this->element('Events/actions', compact('event')); ?>
        </div>

        <div class="description">
            <?php if (!empty($event->images)) : ?>
                <div class="images">
                    <?php foreach ($event->images as $image) : ?>
                        <?= CalendarHelper::thumbnail('small', [
                            'filename' => $image['filename'],
                            'caption' => $image->caption,
                            'group' => 'event' . $event->id,
                        ]) ?>
                        <?php if ($image->caption) : ?>
                            <span class="caption">
                                <?= $image->caption ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($event->description) : ?>
                <?= $this->Text->autolink($event->description, ['escape' => false]) ?>
            <?php endif; ?>

            <?php if ($event->cost || $event->age_restriction) : ?>
                <div class="details">
                    <table>
                        <?php if ($event->cost) : ?>
                            <tr class="cost">
                                <th>Cost:</th>
                                <td>
                                    <?= $event->cost ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ($event->age_restriction) : ?>
                            <tr class="age_restriction detail" id="age_restriction_<?= $event->id ?>">
                                <th>Ages:</th>
                                <td>
                                    <?= $event->age_restriction ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-footer">
            <table class="details">
                <?php if (!empty($event->tags)) : ?>
                    <tr class="tags">
                        <th>Tags:</th>
                        <td>
                            <?= CalendarHelper::eventTags($event) ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($event->series_id) && !empty($event->event_series->title)) : ?>
                    <tr class="tags">
                        <th>Series:</th>
                        <td>
                            <?= $this->Html->link($event->event_series->title, [
                                'controller' => 'EventSeries',
                                'action' => 'view',
                                $event->series_id,
                            ]) ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if ($event->source) : ?>
                    <tr class="source">
                        <th>Source:</th>
                        <td>
                            <?= $this->Text->autoLink($event->source) ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr class="link">
                    <th>Link:</th>
                    <td>
                        <?= $this->Html->link($url, $url); ?>
                    </td>
                </tr>

                <tr class="author">
                    <th>
                        Author:
                    </th>
                    <td>
                        <?php if (!$event->user): ?>
                            Anonymous
                        <?php elseif (!isset($event->user->name)): ?>
                            A user whose account was removed
                        <?php else: ?>
                            <?= $this->Html->link($event->user->name, [
                                'controller' => 'Users',
                                'action' => 'view',
                                'id' => $event->user->id,
                            ]) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</li>
