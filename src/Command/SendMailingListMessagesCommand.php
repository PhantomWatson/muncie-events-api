<?php
namespace App\Command;

use App\Model\Table\MailingListLogTable;
use BadMethodCallException;
use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Mailer\Exception\MissingActionException;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Exception;

/**
 * SendMailingListMessages command.
 *
 * @property \App\Model\Table\EventsTable $Events
 * @property \App\Model\Table\MailingListTable $MailingList
 * @property \Cake\Console\ConsoleIo $io
 * @property boolean $overrideWeekly
 * @property string $recipientEmail
 */
class SendMailingListMessagesCommand extends Command
{
    use MailerAwareTrait;

    const WEEKLY_DELIVERY_DAY = 'Thursday';

    /**
     * Command initialize method
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->Events = TableRegistry::getTableLocator()->get('Events');
        $this->MailingList = TableRegistry::getTableLocator()->get('MailingList');
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser)
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addArgument('mode', [
            'help' => 'daily or weekly',
            'required' => true,
            'choices' => ['daily', 'weekly'],
        ]);

        $parser->addOption('r', [
            'help' => 'Only send to a single recipient, specified by their email address',
        ]);

        $parser->addOption('override-weekly', [
            'help' => 'Overrides the restriction on which day weekly emails can be sent out',
            'boolean' => 'true'
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return void
     * @throws \Exception
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $mode = $args->getArgument('mode');
        $this->io = $io;
        $this->recipientEmail = $args->getOption('r');
        $this->overrideWeekly = $args->getOption('override-weekly');
        if (Configure::read('debug')) {
            $this->io->info(
                'Application is in debug mode, so sending messages to the entire list is disabled. ' .
                'You\'ll need to specify a single email address.'
            );
            while (!$this->recipientEmail) {
                $this->recipientEmail = $this->io->ask('Email address');
            }
        }

        switch ($mode) {
            case 'daily':
                $this->processDaily();

                return;
            case 'weekly':
                $this->processWeekly();

                return;
        }

        throw new Exception("Invalid mode: $mode");
    }

    /**
     * Collects events and recipients for daily emails and sends emails if appropriate
     *
     * @return void
     */
    private function processDaily()
    {
        // Make sure there are recipients
        $recipients = $this->MailingList->getDailyRecipients($this->recipientEmail);
        if (!$recipients->count()) {
            $this->io->out('No recipients found for today');
            if ($this->recipientEmail) {
                $this->io->out('The specified user\'s settings may be preventing an email from being sent out today.');
            }

            return;
        }

        // Make sure there are events to report
        list($y, $m, $d) = [date('Y'), date('m'), date('d')];
        $events = $this->Events
            ->find('published')
            ->find('ordered')
            ->find('withAllAssociated')
            ->where(['date' => "$y-$m-$d"])
            ->toArray();

        $eventCount = count($events);
        $this->io->out(sprintf(
            "%s %s today\n",
            $eventCount,
            __n('event', 'events', $eventCount)
        ));

        if (!$eventCount) {
            $this->MailingList->markDailyAsProcessed(null, MailingListLogTable::NO_EVENTS);
            $this->io->out('No events to inform anyone about today');

            return;
        }

        // Send emails
        foreach ($recipients as $recipient) {
            list($success, $message) = $this->sendDaily($recipient, $events);
            $this->io->{$success ? 'success' : 'error'}($message);
        }
        $this->io->success("\n Done");
    }

    /**
     * Sends the daily version of the event email
     *
     * @param \App\Model\Entity\MailingList $recipient Mailing list subscriber
     * @param \App\Model\Entity\Event[] $events Array of events
     * @return array
     */
    public function sendDaily($recipient, $events)
    {
        $categoryIds = Hash::extract($events, '{n}.category.id');

        $this->io->out('Sending email to ' . $recipient->email . '...');

        // Eliminate any events that this user isn't interested in
        $events = $this->filterEvents($recipient, $events);

        // Make sure there are events left
        if (empty($events)) {
            $this->MailingList->markDailyAsProcessed($recipient, MailingListLogTable::NO_APPLICABLE_EVENTS);

            $selected = 'Selected: ' . Hash::extract($recipient->categories, '{n}.id');
            $available = 'Available: ' . implode(', ', $categoryIds);

            return [
                true,
                "No events to report, resulting from $recipient->email's settings ($selected; $available)",
            ];
        }

        try {
            $this->getMailer('MailingList')->send('daily', [$recipient, $events]);
            $this->MailingList->markDailyAsProcessed($recipient, MailingListLogTable::EMAIL_SENT);

            return [true, 'Email sent to ' . $recipient->email];
        } catch (MissingActionException | BadMethodCallException $e) {
            $this->MailingList->markDailyAsProcessed($recipient, MailingListLogTable::ERROR_SENDING);

            return [false, 'Error sending email to ' . $recipient->email . ': ' . $e->getMessage()];
        }
    }

    /**
     * Returns an array of events filtered according to the recipient's mailing list settings
     *
     * @param \App\Model\Entity\MailingList $recipient Subscribers
     * @param \App\Model\Entity\Event[] $events Array of events
     * @return \App\Model\Entity\Event[]
     */
    private function filterEvents($recipient, $events)
    {
        if ($recipient->all_categories) {
            return $events;
        }

        $allowedCategoryIds = Hash::extract($recipient->categories, '{n}.id');
        foreach ($events as $k => $event) {
            $eventIsAllowed = in_array($event->category->id, $allowedCategoryIds);
            if (!$eventIsAllowed) {
                unset($events[$k]);
            }
        }

        return $events;
    }

    /**
     * Collects events and recipients for weekly emails and sends emails if appropriate
     *
     * @return void
     */
    private function processWeekly()
    {
        // Make sure that today is the correct day
        if (!$this->overrideWeekly && !$this->isWeeklyDeliveryDay()) {
            $this->io->out('Today is not the day of the week designated for delivering weekly emails.');

            return;
        }

        // Make sure there are recipients
        $recipients = $this->MailingList->getWeeklyRecipients($this->recipientEmail);
        if (!$recipients->count()) {
            $this->io->out('No recipients found for this week');
            if ($this->recipientEmail) {
                $this->io->out('The specified user\'s settings may be preventing an email from being sent out today.');
            }
        }

        // Make sure there are events to report
        $events = $this->Events
            ->find('published')
            ->find('ordered')
            ->find('withAllAssociated')
            ->find('upcomingWeek')
            ->toArray();
        if (empty($events)) {
            $this->MailingList->markWeeklyAsProcessed(null, MailingListLogTable::NO_EVENTS);
            $this->io->out('No events to inform anyone about this week');

            return;
        }

        // Send emails
        foreach ($recipients as $recipient) {
            list($success, $message) = $this->sendWeekly($recipient, $events);
            $this->io->{$success ? 'success' : 'error'}($message);
        }
        $this->io->success("\nDone");
    }

    /**
     * Returns TRUE if today is the day that weekly emails should be delivered
     *
     * @return bool
     */
    private function isWeeklyDeliveryDay()
    {
        return date('l') == self::WEEKLY_DELIVERY_DAY;
    }

    /**
     * Sends the weekly version of the event mailing list email
     *
     * @param \App\Model\Entity\MailingList $recipient Subscriber entity
     * @param \App\Model\Entity\Event[] $events Array of events
     * @return array:boolean string
     */
    private function sendWeekly($recipient, $events)
    {
        $categoryIds = Hash::extract($events, '{n}.category.id');

        // Eliminate any events that this user isn't interested in
        $events = $this->filterEvents($recipient, $events);

        // Make sure there are events left
        if (empty($events)) {
            // No events to report to this user today.
            $this->MailingList->markWeeklyAsProcessed($recipient, MailingListLogTable::NO_APPLICABLE_EVENTS);
            $selected = 'Selected: ' . Hash::extract($recipient->categories, '{n}.id');
            $available = 'Available: ' . implode(', ', $categoryIds);

            return [
                true,
                "No events to report, resulting from $recipient->email's settings ($selected; $available)",
            ];
        }

        try {
            $this->getMailer('MailingList')->send('weekly', [$recipient, $events]);
            $this->MailingList->markWeeklyAsProcessed($recipient, MailingListLogTable::EMAIL_SENT);

            return [true, 'Email sent to ' . $recipient->email];
        } catch (MissingActionException | BadMethodCallException $e) {
            $this->MailingList->markWeeklyAsProcessed($recipient, MailingListLogTable::ERROR_SENDING);

            return [false, "Error sending email to $recipient->email: " . $e->getMessage()];
        }
    }
}
