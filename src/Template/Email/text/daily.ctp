<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var \App\Model\Entity\Event[] $events
 * @var array $settingsDisplay
 */

use Cake\Routing\Router;

?>

Events for <?= date('l, F jS, Y') ?> brought to you by https://MuncieEvents.com

<?php if ($recipient->new_subscriber): ?>
    <?= $this->element('MailingList/welcome_text') ?>
<?php endif; ?>


<?php
    foreach ($events as $event) {
        echo sprintf(
            "%s: %s\n[%s]\n%s",
            strtoupper($event->category->name),
            $event->title,
            Router::url([
                'controller' => 'Events',
                'action' => 'view',
                'id' => $event->id
            ], true),
            $event->time_start->format('g:ia')
        );
        if ($event->time_end) {
            echo ' - ' . $event->time_end->format('g:ia');
        }
        echo " @ $event->location\n\n";
    }
?>

Your settings...
Frequency: <?= $settingsDisplay['frequency'] ?>

Events: <?= $settingsDisplay['event_types'] ?>

This email was sent to <?= $recipient->email ?> on behalf of https://MuncieEvents.com

Add Event: <?= Router::url([
    'controller' => 'Events',
    'action' => 'add'
], true) ?>

Change Settings: <?= Router::url([
    'controller' => 'MailingList',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash
], true) ?>

Unsubscribe: <?= Router::url([
    'controller' => 'MailingList',
    'action' => 'settings',
    $recipient->id,
    $recipient->hash,
    '?' => 'unsubscribe'
], true) ?>
