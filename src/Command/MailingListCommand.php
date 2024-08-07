<?php
namespace App\Command;

use App\Model\Entity\Event;
use App\Model\Entity\MailingList;
use App\Model\Table\EventsTable;
use App\Model\Table\MailingListLogTable;
use App\Model\Table\MailingListTable;
use BadMethodCallException;
use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Exception\MissingActionException;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Exception;
use hbattat\VerifyEmail;

/**
 * SendMailingListMessages command.
 *
 * @property EventsTable $Events
 * @property MailingListTable $MailingList
 * @property ConsoleIo $io
 * @property boolean $overrideWeekly
 * @property string $recipientEmail
 */
class MailingListCommand extends Command
{
    use MailerAwareTrait;

    const WEEKLY_DELIVERY_DAY = 'Thursday';
    const DEFAULT_RECIPIENT_EMAIL = 'graham@phantomwatson.com';

    /**
     * The limit of how many recipients will be emailed during this iteration
     *
     * Implemented due to InMotion's 250 messages per hour limit
     */
    const RECIPIENT_LIMIT = 10;

    /**
     * Command initialize method
     *
     * @return void
     */
    public function initialize(): void
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
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->addArgument('action', [
            'help' => 'send_daily or send_weekly',
            'required' => true,
            'choices' => ['send_daily', 'send_weekly'],
        ]);

        $parser->addOption('r', [
            'help' => 'Only send to a single recipient, specified by their email address',
        ]);

        $parser->addOption('override-weekly', [
            'help' => 'Overrides the restriction on which day weekly emails can be sent out',
            'boolean' => true,
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param Arguments $args The command arguments.
     * @param ConsoleIo $io The console io
     * @return int
     * @throws Exception
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $action = $args->getArgument('action');
        $this->io = $io;
        $this->recipientEmail = $args->getOption('r');
        $this->overrideWeekly = $args->getOption('override-weekly');
        if (Configure::read('debug')) {
            $this->io->info(
                'Application is in debug mode, so sending messages to the entire list is disabled. ' .
                'You\'ll need to specify a single email address.'
            );
            while (!$this->recipientEmail) {
                $this->recipientEmail = $this->io->ask('Email address', self::DEFAULT_RECIPIENT_EMAIL);
            }
        }

        switch ($action) {
            case 'send_daily':
                return $this->processDaily();
            case 'send_weekly':
                return $this->processWeekly();
        }

        $io->error("Invalid action: $action");
        return static::CODE_ERROR;
    }

    /**
     * Collects events and recipients for daily emails and sends emails if appropriate
     *
     * @return int
     */
    private function processDaily(): int
    {
        // Make sure there are recipients
        $recipients = $this->MailingList->getDailyRecipients($this->recipientEmail, self::RECIPIENT_LIMIT);
        if (!$recipients->count()) {
            $this->io->out('No recipients found for today');
            if ($this->recipientEmail) {
                $this->io->out(
                    'The specified user\'s settings may be preventing an email from being sent out today, ' .
                    'or they may not be subscribed.'
                );
            }

            return static::CODE_SUCCESS;
        }

        // Make sure there are events to report
        $timezone = Configure::read('localTimezone');
        $events = $this->Events
            ->find('published')
            ->find('ordered')
            ->find('withAllAssociated')
            ->where(['date' => (new FrozenTime('now', $timezone))->format('Y-m-d')])
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

            return static::CODE_SUCCESS;
        }

        // Send emails
        foreach ($recipients as $recipient) {
            list($success, $message) = $this->sendDaily($recipient, $events);
            $this->io->{$success ? 'success' : 'error'}($message);
        }
        $this->io->success("\n Done");

        return static::CODE_SUCCESS;
    }

    /**
     * Sends the daily version of the event email
     *
     * @param MailingList $recipient Mailing list subscriber
     * @param Event[] $events Array of events
     * @return array
     */
    public function sendDaily(MailingList $recipient, array $events): array
    {
        $categoryIds = Hash::extract($events, '{n}.category.id');

        $this->io->out('Sending email to ' . $recipient->email . '...');

        // Eliminate any events that this user isn't interested in
        $events = $this->filterEvents($recipient, $events);

        // Make sure there are events left
        if (empty($events)) {
            $this->MailingList->markDailyAsProcessed($recipient, MailingListLogTable::NO_APPLICABLE_EVENTS);

            $selected = 'Selected: ' . implode(', ', Hash::extract($recipient->categories, '{n}.id'));
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
     * @param MailingList $recipient Subscribers
     * @param Event[] $events Array of events
     * @return Event[]
     */
    private function filterEvents(MailingList $recipient, array $events): array
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
     * @return int
     */
    private function processWeekly(): int
    {
        // Make sure that today is the correct day
        if (!$this->overrideWeekly && !$this->isWeeklyDeliveryDay()) {
            $this->io->out('Today is not the day of the week designated for delivering weekly emails.');

            return static::CODE_SUCCESS;
        }

        // Make sure there are recipients
        $recipients = $this->MailingList->getWeeklyRecipients($this->recipientEmail, self::RECIPIENT_LIMIT);
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

            return static::CODE_SUCCESS;
        }

        // Send emails
        foreach ($recipients as $recipient) {
            list($success, $message) = $this->sendWeekly($recipient, $events);
            $this->io->{$success ? 'success' : 'error'}($message);
        }
        $this->io->success("\nDone");
        return static::CODE_SUCCESS;
    }

    /**
     * Returns TRUE if today is the day that weekly emails should be delivered
     *
     * @return bool
     */
    private function isWeeklyDeliveryDay(): bool
    {
        $timezone = Configure::read('localTimezone');

        return (new FrozenTime('now', $timezone))->format('l') == self::WEEKLY_DELIVERY_DAY;
    }

    /**
     * Sends the weekly version of the event mailing list email
     *
     * @param MailingList $recipient Subscriber entity
     * @param Event[] $events Array of events
     * @return array:boolean string
     */
    private function sendWeekly(MailingList $recipient, array $events): array
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

    /**
     * Returns TRUE if the email address is verified, or an error message string
     *
     * @param string $email Email address to check
     * @return string|true
     */
    private function verifyEmail(string $email): bool|string
    {
        $from = Configure::read('automailer_address');
        $verification = new VerifyEmail($email, $from);
        if ($verification->verify()) {
            return true;
        }
        $errors = $verification->get_errors();
        var_dump($verification->get_debug());
        return "Error verifying $email: " . implode(PHP_EOL . ' - ', $errors);
    }
}
