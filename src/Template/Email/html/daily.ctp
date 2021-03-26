<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var \App\Model\Entity\Event[] $events
 * @var array $settingsDisplay
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$timezone = Configure::read('localTimezone');
$now = new FrozenTime('now', $timezone);

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

<div class="wrapper">
    <h3 class="day">
        <?= sprintf(
            '%s <span class="date">%s<sup>%s</sup></span>',
            $now->format('l'),
            $now->format('F j'),
            $now->format('S')
        ) ?>
    </h3>
    <?php foreach ($events as $event): ?>
        <p class="event">
            <?= $this->Icon->category($event->category->name, 'email'); ?>

            <?= $this->Html->link(
                $event->title,
                [
                    'controller' => 'events',
                    'action' => 'view',
                    'id' => $event->id,
                    '_full' => true,
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
</div>

<?= $this->element('MailingList/footer') ?>
