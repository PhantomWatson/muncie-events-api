<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var array $settingsDisplay
 */
?>

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
            '_full' => true,
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
            '_full' => true,
        ]
    ) ?>
    &nbsp; | &nbsp;
    <?= $this->Html->link(
        'Unsubscribe',
        [
            'controller' => 'MailingList',
            'action' => 'unsubscribe',
            $recipient->id,
            $recipient->hash,
            '_full' => true,
        ]
    ) ?>
</p>
