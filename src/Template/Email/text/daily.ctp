<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var \App\Model\Entity\Event[] $events
 * @var array $settingsDisplay
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

$timezone = Configure::read('localTimezone');
$now = new FrozenTime('now', $timezone);
?>

Events for <?= $now->format('l, F jS, Y') ?> brought to you by https://MuncieEvents.com

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
                'id' => $event->id,
            ], true),
            $event->time_start->format('g:ia')
        );
        if ($event->time_end) {
            echo ' - ' . $event->time_end->format('g:ia');
        }
        echo " @ $event->location\n\n";
    }
?>

<?= $this->element('MailingList/footer_text') ?>
