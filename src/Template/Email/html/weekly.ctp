<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var \App\Model\Entity\Event[] $events
 * @var array $settingsDisplay
 */

use App\View\Helper\CalendarHelper;
use Cake\I18n\FrozenDate;

$eventsByDate = CalendarHelper::arrangeByDate($events);
?>
<style>
    <?php include(ROOT . DS . 'webroot' . DS . 'css' . DS . 'email.css'); ?>
</style>

<h1>
    <a href="https://muncieevents.com">
        <img src="https://muncieevents.com/img/email_logo.png" alt="Muncie Events" />
    </a>
</h1>

<?php if ($recipient->new_subscriber): ?>
    <?= $this->element('MailingList/welcome') ?>
<?php endif; ?>

<div>
    <?php foreach ($eventsByDate as $date => $daysEvents) : ?>
        <?php
            if (empty($daysEvents)) {
                continue;
            }
        ?>
        <h3 class="day">
            <?php
                $dateObj = new FrozenDate($date);
                echo sprintf(
                    '%s <span class="date">%s<sup>%s</sup></span>',
                    $dateObj->format('l'),
                    $dateObj->format('F j'),
                    $dateObj->format('S')
                );
            ?>
        </h3>
        <?php foreach ($daysEvents as $event) : ?>
            <p class="event">
                <?= $this->Icon->category($event->category->name, 'email'); ?>

                <?= $this->Html->link(
                    $event->title,
                    [
                        'controller' => 'events',
                        'action' => 'view',
                        'id' => $event->id,
                        'fullBase' => true,
                    ]
                ) ?>
                <br />
                <?= $event->time_start->format('g:ia') ?>
                <?php if ($event->time_end): ?>
                    - <?= $event->time_end->format('g:ia') ?>
                <?php endif; ?>
                @
                <?= $event->location ?>
            </p>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<?= $this->element('MailingList/footer') ?>

