<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var \App\Model\Entity\Event[] $events
 * @var array $settingsDisplay
 */
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
    <h3 class="day">
        <?= sprintf(
            '%s <span class="date">%s<sup>%s</sup></span>',
            date('l'),
            date('F j'),
            date('S')
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
</div>

<p class="footnote">
    <strong>Your settings...</strong><br />
    Frequency: <?= $settingsDisplay['frequency'] ?><br />
    Events: <?= $settingsDisplay['eventTypes'] ?>
</p>

<p class="footnote">
    This email was sent to <?= $recipient->email ?>
    on behalf of <a href="https://muncieevents.com">MuncieEvents.com</a>
    <br />
    <?= $this->Html->link(
        'Add Event',
        [
            'controller' => 'Events',
            'action' => 'add',
            'fullBase' => true,
        ]
    ) ?>
    &nbsp; | &nbsp;
    <?= $this->Html->link(
        'Change Settings',
        [
            'controller' => 'MailingList',
            'action' => 'settings',
            $recipient->id,
            $recipient->hash,
            'fullBase' => true,
        ]
    ) ?>
    &nbsp; | &nbsp;
    <?= $this->Html->link(
        'Unsubscribe',
        [
            'controller' => 'MailingList',
            'action' => 'settings',
            $recipient->id,
            $recipient->hash,
            '?' => 'unsubscribe',
            'fullBase' => true,
        ]
    ) ?>
</p>
