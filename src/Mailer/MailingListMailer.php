<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;

class MailingListMailer extends Mailer
{
    /**
     * Defines a daily events mailing list email
     *
     * @param \App\Model\Entity\MailingList $recipient Mailing list subscriber
     * @param \App\Model\Entity\Event[] $events Array of events
     * @return void
     * @throws InternalErrorException
     */
    public function daily($recipient, $events)
    {
        $this->viewBuilder()->setTemplate('daily');
        $date = (new FrozenTime('now', Configure::read('localTimezone')))->format('l, M j');
        $this
            ->setTo($recipient->email)
            ->setFrom(Configure::read('automailer_address'), 'Muncie Events')
            ->setSubject("Today in Muncie: $date")
            ->setViewVars([
                'events' => $events,
                'recipient' => $recipient,
                'settingsDisplay' => $this->getSettingsDisplay($recipient),
            ])
            ->setEmailFormat('both')
            ->setDomain('muncieevents.com');
    }

    /**
     * Defines a weekly events mailing list email
     *
     * @param \App\Model\Entity\MailingList $recipient Mailing list subscriber
     * @param \App\Model\Entity\Event[] $events Array of events
     * @return void
     * @throws InternalErrorException
     */
    public function weekly($recipient, $events)
    {
        $this->viewBuilder()->setTemplate('weekly');
        $date = (new FrozenTime('now', Configure::read('localTimezone')))->format('l, M j');
        $this
            ->setTo($recipient->email)
            ->setFrom(Configure::read('automailer_address'), 'Muncie Events')
            ->setSubject("Upcoming Week in Muncie: $date")
            ->setViewVars([
                'events' => $events,
                'recipient' => $recipient,
                'settingsDisplay' => $this->getSettingsDisplay($recipient),
            ])
            ->setEmailFormat('both')
            ->setDomain('muncieevents.com');
    }

    /**
     * Returns information about this subscriber's mailing list settings
     *
     * @param \App\Model\Entity\MailingList $recipient Mailing list subscriber
     * @return array
     */
    private function getSettingsDisplay($recipient)
    {
        // Categories
        if ($recipient->all_categories) {
            $eventTypes = 'All events';
        } else {
            $selectedCategories = $recipient->categories;
            $categoryNames = [];
            foreach ($selectedCategories as $selectedCategory) {
                $categoryNames[] = $selectedCategory->name;
            }
            $eventTypes = 'Only ' . $this->toList($categoryNames);
        }

        // Frequency
        $days = [
            'sun' => 'Sunday',
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday',
            'sat' => 'Saturday',
        ];
        $selectedDays = [];
        foreach (array_keys($days) as $day) {
            if ($recipient->{"daily_$day"}) {
                $selectedDays[] = $days[$day];
            }
        }
        $dayCount = count($selectedDays);
        if ($dayCount == 7) {
            $frequency = 'Daily';
            if ($recipient->weekly) {
                $frequency .= ' and weekly';
            }
        } elseif ($dayCount > 0) {
            $frequency = 'Daily on ' . $this->toList($selectedDays);
            if ($recipient->weekly) {
                $frequency .= ' and weekly';
            }
        } else {
            $frequency = $recipient->weekly ? 'Weekly' : '?';
        }

        return compact('eventTypes', 'frequency');
    }

    /**
     * A duplication of the TextHelper method with serial comma added
     *
     * @param string[] $list List of objects to join
     * @param string $and Conjunction to put at end of series
     * @param string $separator Punctuation mark, such as a comma
     * @return string
     */
    private function toList($list, $and = 'and', $separator = ', ')
    {
        if (count($list) < 2) {
            return array_pop($list);
        }

        $separator = (count($list) > 2) ? $separator : ' ';

        return implode($separator, array_slice($list, null, -1)) . $and . array_pop($list);
    }
}
