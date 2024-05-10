<?php
/**
 * @var \App\Model\Entity\MailingList $recipient
 * @var \App\Model\Entity\Event[] $events
 * @var array $settingsDisplay
 */

use App\View\Helper\CalendarHelper;
use Cake\I18n\FrozenDate;
use Cake\Routing\Router;
$eventsByDate = CalendarHelper::arrangeByDate($events);
?>
Upcoming Events
brought to you by https://MuncieEvents.com

<?php if ($recipient->new_subscriber): ?>
    <?= $this->element('MailingList/welcome_text') ?>
<?php endif; ?>

<?php
foreach ($eventsByDate as $date => $daysEvents) {
    if (empty($daysEvents)) {
        continue;
    }
    echo sprintf(
        "%s\n--------------\n",
        (new FrozenDate($date))->format('l, F jS')
    );
    foreach ($daysEvents as $event) {
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
    echo "\n\n";
}

echo $this->element('MailingList/footer_text');
