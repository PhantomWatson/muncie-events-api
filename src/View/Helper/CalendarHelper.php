<?php
namespace App\View\Helper;

use App\Model\Entity\Event;
use Cake\Chronos\Date;
use Cake\Core\Configure;
use Cake\ORM\ResultSet;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * Class CalendarHelper
 * @package App\View\Helper
 * @property Helper\HtmlHelper $Html
 */
class CalendarHelper extends Helper
{
    public array $helpers = ['Html'];

    /**
     * Returns an array events, grouped by their date
     *
     * Return value format: [
     *      'YYYY-MM-DD' => [$event1, $event2, ...],
     *      'YYYY-MM-DD' => [$event1, $event2, ...]
     * ]
     *
     * @param ResultSet|Event[] $events Array of Event objects
     * @return array
     */
    public static function arrangeByDate($events)
    {
        if (!is_array($events)) {
            $events = $events->toArray();
        }
        $retval = [];

        foreach ($events as $event) {
            $date = $event->date->format('Y-m-d');
            $retval[$date][] = $event;
        }

        ksort($retval);

        return $retval;
    }

    /**
     * Returns the date that follows the last date in this set of events in the format YYYY-MM-DD
     *
     * @param ResultSet|Event[] $events Array of events
     * @return string|null
     */
    public static function getNextStartDate($events)
    {
        if (!$events) {
            return null;
        }
        if (!is_array($events)) {
            $events = $events->toArray();
        }

        // If $events is arranged by date
        if (is_string(array_keys($events)[0])) {
            $dates = array_keys($events);
            $lastDate = new \Cake\Chronos\ChronosDate(end($dates));

        // If $events is a flat array
        } else {
            $lastEvent = end($events);
            $lastDate = $lastEvent->date;
        }

        return $lastDate->addDay(1)->format('Y-m-d');
    }

    /**
     * Returns an <h2> header describing the provided date, e.g. Today; Tomorrow; This Wednesday; December 6, 1984
     *
     * @param string $date Date string in YYYY-MM-DD format
     * @return string
     */
    public static function getDateHeader(string $date)
    {
        $timezone = Configure::read('localTimezone');
        $today = (new \Cake\I18n\DateTime('now', $timezone))->format('Y-m-d');
        $tomorrow = (new \Cake\I18n\DateTime('now + 1 day', $timezone))->format('Y-m-d');
        $namedDates = [
            $today => 'Today',
            $tomorrow => 'Tomorrow',
        ];
        $endOfWeek = (new \Cake\I18n\DateTime('now + 6 days', $timezone))->format('Y-m-d');
        $thisWeek = ($date >= $today && $date < $endOfWeek);

        if (isset($namedDates[$date])) {
            $day = $namedDates[$date];
        } else {
            $day = ($thisWeek ? 'This ' : '') . (new \Cake\I18n\DateTime($date, $timezone))->format('l');
        }

        $headerShortDate = sprintf(
            '<h2 class="short_date">%s</h2>',
            date('M j, Y', strtotime($date))
        );
        $headerDay = sprintf('<h2 class="day">%s</h2>', $day);

        return $headerShortDate . $headerDay;
    }

    /**
     * Outputs either a thumbnail (square) image or a small (width-limited) image
     *
     * @param string $type 'small' or 'tiny'
     * @param array $params for the image
     * @return string
     */
    public static function thumbnail(string $type, array $params)
    {
        // Don't show image if filename is unspecified
        if (!isset($params['filename'])) {
            return '';
        }

        $basePath = Configure::read('eventImagePath');
        $filename = $params['filename'];
        $thumbnailPath = $basePath . DS . $type . DS . $filename;

        // Don't show image if it doesn't exist
        if (!file_exists($thumbnailPath)) {
            return '';
        }

        $altText = $params['caption'] ?? $filename;
        $class = "thumbnail tn_$type " . ($params['class'] ?? '');
        $baseUrl = Configure::read('eventImageBaseUrl');
        $url = $baseUrl . $type . '/' . $filename;
        $image = sprintf(
            '<img src="%s" class="%s" alt="%s" title="%s" />',
            $url,
            $class,
            $altText,
            $params['caption'] ?? ''
        );

        // Return unlinked thumbnail if full-size image doesn't exist
        $fullPath = $basePath . DS . 'full' . DS . $filename;
        if (!file_exists($fullPath)) {
            return $image;
        }

        // Link to full image
        $rel = isset($params['group'])
            ? sprintf('popup[%s]', $params['group'])
            : 'popup';
        $url = $baseUrl . 'full/' . $filename;

        return sprintf(
            '<a href="%s" rel="%s" class="%s" >%s</a>',
            $url,
            $rel,
            $class,
            $image
        );
    }

    /**
     * Returns a string describing the start time (and end time, if applicable) of this event
     *
     * @param Event $event Event entity
     * @return string
     */
    public static function time($event)
    {
        $start = $event->time_start;
        $isOnHour = substr($start->i18nFormat(), -5, 2) == '00';
        $pattern = $isOnHour ? 'ga' : 'g:ia';
        $retval = $start->format($pattern);

        $end = $event->time_end;
        if ($end) {
            $isOnHour = substr($end->i18nFormat(), -5, 2) == '00';
            $pattern = $isOnHour ? 'ga' : 'g:ia';
            $retval .= ' to ' . $end->format($pattern);
        }

        return $retval;
    }

    /**
     * Returns the URL used for loading an event into Google Calendar
     *
     * @param Event $event Event entity
     * @return string
     */
    public static function getGoogleCalendarUrl(Event $event)
    {
        // Clean up and truncate description
        $eventUrl = Router::url([
            'controller' => 'Events',
            'action' => 'view',
            'id' => $event['id'],
            'plugin' => false,
            'prefix' => false,
        ], true);
        $description = strip_tags($event['description']);
        $description = str_replace('&nbsp;', '', $description);
        $description = Text::truncate(
            $description,
            1000,
            [
                'ellipsis' => "... (continued at $eventUrl)",
                'exact' => false,
                'html' => false,
            ]
        );

        $address = trim($event['address']) ?: 'Muncie, IN';
        if (mb_stripos($address, 'Muncie') === false) {
            $address .= ', Muncie, IN';
        }

        $location = sprintf(
            '%s%s (%s)',
            $event['location'],
            $event['location_details'] ? ', ' . $event['location_details'] : '',
            $address
        );

        $startTimeString = self::getDatetimeForGoogleCal($event->date, $event->time_start);
        $endTimeString = $event->time_end
            ? self::getDatetimeForGoogleCal($event->date, $event->time_end)
            : $startTimeString;

        return 'https://calendar.google.com/calendar/render?action=TEMPLATE' .
            '&text=' . urlencode($event['title']) .
            '&dates=' . sprintf('%s/%s', $startTimeString, $endTimeString) .
            '&ctz=America/New_York' .
            '&details=' . urlencode($description) .
            '&location=' . urlencode($location) .
            '&trp=false' .
            '&sprop=' . urlencode('Muncie Events') .
            '&sprop=name:' . urlencode('https://muncieevents.com');
    }

    /**
     * Returns a local-timezone datetime in YYYYMMDDToHHmmSSZ format UTC time for Google Calendar
     *
     * Reference: https://github.com/InteractionDesignFoundation/add-event-to-calendar-docs/blob/master/services/google.md
     *
     * @param \Cake\I18n\Date $date Event date
     * @param \Cake\I18n\DateTime $time Event start or end time
     * @return string
     */
    public static function getDatetimeForGoogleCal($date, $time)
    {
        $newTime = (new \Cake\I18n\DateTime($time))
            ->setDate($date->year, $date->month, $date->day)
            ->setTimezone(Event::TIMEZONE);

        return sprintf(
            '%sT%s',
            $newTime->i18nFormat('yyyyMMdd', 'UTC'),
            $newTime->i18nFormat('HHmmss', 'UTC')
        );
    }

    /**
     * Returns a linked list of tags
     *
     * @param Event $event Event entity
     * @return string
     */
    public static function eventTags($event)
    {
        $links = [];
        foreach ($event->tags as $tag) {
            $url = Router::url([
                'controller' => 'Events',
                'action' => 'tag',
                'slug' => $tag->id . '-' . Text::slug($tag->name),
            ]);
            $links[] = sprintf('<a href="%s">%s</a>', $url, $tag->name);
        }

        return implode(', ', $links);
    }

    /**
     * Returns a link to view events on the previous day
     *
     * @param string $date Date in format YYYY-MM-DD
     * @return string
     */
    public function prevDay($date)
    {
        list($year, $month, $day) = explode('-', $date);
        $timestamp = mktime(0, 0, 0, $month, $day - 1, $year);

        return $this->Html->link(
            '&larr; Previous Day',
            [
                'controller' => 'Events',
                'action' => 'day',
                date('m', $timestamp),
                date('d', $timestamp),
                date('Y', $timestamp),
            ],
            ['escape' => false]
        );
    }

    /**
     * Returns a link to view events on the next day
     *
     * @param string $date Date in format YYYY-MM-DD
     * @return string
     */
    public function nextDay($date)
    {
        list($year, $month, $day) = explode('-', $date);
        $timestamp = mktime(0, 0, 0, $month, $day + 1, $year);

        return $this->Html->link(
            'Next Day &rarr;',
            [
                'controller' => 'Events',
                'action' => 'day',
                date('m', $timestamp),
                date('d', $timestamp),
                date('Y', $timestamp),
            ],
            ['escape' => false]
        );
    }
}
