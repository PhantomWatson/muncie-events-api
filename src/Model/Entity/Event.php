<?php
namespace App\Model\Entity;

use App\Model\Table\TagsTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use DateTime;
use Exception;
use Sabre\VObject;

/**
 * Event Entity
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $location
 * @property string $location_details
 * @property string $location_slug
 * @property string $address
 * @property int $user_id
 * @property int $category_id
 * @property int $series_id
 * @property FrozenDate $date
 * @property FrozenTime $time_start
 * @property FrozenTime|null $time_end
 * @property string $age_restriction
 * @property string $cost
 * @property string $source
 * @property bool $published
 * @property int $approved_by
 * @property FrozenTime $created
 * @property FrozenTime $modified
 * @property string $location_medium 'physical' or 'virtual'
 * @property string $description_plaintext
 *
 * @property User $user
 * @property Category $category
 * @property EventSeries $event_series
 * @property Image[] $images
 * @property Tag[] $tags
 */
class Event extends Entity
{
    const TIMEZONE = 'America/Indiana/Indianapolis';
    const VIRTUAL_LOCATION = 'Virtual Event';
    const VIRTUAL_LOCATION_SLUG = 'virtual-event';

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'title' => true,
        'description' => true,
        'location' => true,
        'location_details' => true,
        'address' => true,
        'user_id' => true,
        'category_id' => true,
        'series_id' => true,
        'date' => true,
        'time_start' => true,
        'time_end' => true,
        'age_restriction' => true,
        'cost' => true,
        'source' => true,
        'published' => true,
        'approved_by' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'category' => true,
        'event_series' => true,
        'images' => true,
        'tags' => true,
    ];

    /**
     * Fields that API users are not allowed to directly update
     *
     * @var array
     */
    public $updateProtectedFields = [
        'published',
        'approved_by',
        'created',
        'modified',
        'user_id',
        'series_id',
        'user',
        'category',
        'event_series',
        'tags',
    ];

    /**
     * Takes an array of image data and sets proper join data for the next save operation
     *
     * @param array $imagesData Array of ['id' => $imageId, 'caption' => ...] or [$imageId => $caption] arrays
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function setImageJoinData($imagesData)
    {
        $this->images = [];
        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        $weight = 1;
        foreach ($imagesData as $key => $value) {
            if (is_array($value)) {
                $imageId = $value['id'];
                $caption = $value['caption'] ?? '';
            } else {
                $imageId = $key;
                $caption = $value;
            }

            try {
                $image = $imagesTable->get($imageId);
            } catch (RecordNotFoundException $e) {
                throw new BadRequestException("Invalid image ID selected (#$imageId)");
            }

            $image->_joinData = new Entity([
                'weight' => $weight,
                'caption' => $caption,
            ]);
            $this->images[] = $image;
            $weight++;
        }
    }

    /**
     * Returns a full RFC 3339 string with the correct timezone offset, year, month, and day
     *
     * e.g. "2019-12-06T10:00:00+05:00".
     *
     * @param FrozenDate $date Date object
     * @param FrozenTime|null $localTime Time object
     * @return string|null
     * @throws Exception
     */
    public static function getDatetime($date, $localTime)
    {
        $correctedTime = self::getCorrectedTime($date, $localTime);

        return $correctedTime ? $correctedTime->toRfc3339String() : null;
    }

    /**
     * Returns a Time object with correct UTC offset representing the provided time
     *
     * Example: Events taking place at 10am in Indiana are stored as "10:00:00", which is has no timezone info and may
     * be assumed incorrectly to be UTC. This method returns an object with the correct timezone offset, time, and date
     *
     * @param FrozenDate $date Date object
     * @param FrozenTime|null $localTime Time object
     * @return Time|null
     * @throws Exception
     */
    private static function getCorrectedTime($date, $localTime)
    {
        if (!$localTime) {
            return null;
        }

        // Create a time object with the correct timezone set
        $timeString = $localTime->toDateTimeString();
        $correctedTime = new Time($timeString);
        $correctedTime->timezone(self::TIMEZONE);

        // Add correct date
        $correctedTime
            ->day($date->day)
            ->month($date->month)
            ->year($date->year);

        // Change from Indiana time to UTC time
        $offset = timezone_offset_get(timezone_open(self::TIMEZONE), new DateTime($localTime));
        $isNegOffset = stripos($offset, '-') === 0;
        $modification = str_replace(
                $isNegOffset ? '-' : '+',
                $isNegOffset ? '+' : '-',
                $offset
            ) . ' seconds';
        $correctedTime->modify($modification);

        return $correctedTime;
    }

    /**
     * Returns a full local-time ISO 8601 string without offset info (because that's specified in a TZID property)
     *
     * @param $date
     * @param $localTime
     * @return string|null
     * @throws \Exception
     */
    public static function getDatetimeForIcal($date, $localTime) {
        $correctedTime = self::getCorrectedTime($date, $localTime);
        if (!$correctedTime) {
            return null;
        }

        return $correctedTime->format('Ymd\THis');
    }


    /**
     * Sets the event to approved if $user (the user submitting the form) is an administrator
     *
     * @param array|User|null $user The user submitting the form (not necessarily the original event author)
     * @return void
     * @throws InternalErrorException
     */
    public function autoApprove($user)
    {
        if (!$user) {
            return;
        }
        if (!is_array($user)) {
            $user = $user->toArray();
        }
        if (isset($user['role']) && $user['role'] == 'admin') {
            if (!isset($user['id'])) {
                throw new InternalErrorException('Cannot approve event. Administrator ID unknown.');
            }
            $this->approved_by = $user['id'];
        }
    }

    /**
     * Sets the event to published if the user submitting the form qualifies
     *
     * @param array|User|null $user The user submitting the form (not necessarily the original event author)
     * @return void
     * @throws InternalErrorException
     */
    public function autoPublish($user)
    {
        $this->published = $this->userIsAutoPublishable($user);
    }

    /**
     * Returns TRUE if the specified user should have their events automatically published
     *
     * @param array|User|null $user Array of user record info, empty for anonymous users
     * @return bool
     */
    public function userIsAutoPublishable($user)
    {
        if (!$user) {
            return false;
        }

        if (!is_array($user)) {
            $user = $user->toArray();
        }
        if (!isset($user['id'])) {
            return false;
        }

        if (isset($user['role']) && $user['role'] == 'admin') {
            return true;
        }

        // Users who have submitted events that were published by admins have all subsequent events auto-published
        return $this->userHasPublished($user['id']);
    }

    /**
     * Returns TRUE if the user with the specified ID has any associated published events
     *
     * @param int $userId User ID
     * @return bool
     */
    private function userHasPublished($userId)
    {
        $eventsTable = TableRegistry::getTableLocator()->get('Events');

        return $eventsTable->exists([
            'published' => true,
            'user_id' => $userId,
        ]);
    }

    /**
     * Adds tag entities to this event entity, creating new records if necessary
     *
     * @param int[] $tagIds An array of tag IDs
     * @param string|string[] $tagNames An array of tag names or a comma-delimited string
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function processTags($tagIds, $tagNames)
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $this->tags = $this->tags ?? [];
        foreach ($tagIds as $tagId) {
            try {
                $this->tags[] = $tagsTable->get($tagId);
            } catch (RecordNotFoundException $e) {
                throw new BadRequestException('Invalid tag ID selected (#' . $tagId . ')');
            }
        }

        if (!is_array($tagNames)) {
            $tagNames = explode(',', $tagNames);
        }
        $tagNames = array_map('trim', $tagNames);
        $tagNames = array_map('mb_strtolower', $tagNames);
        $tagNames = array_unique($tagNames);

        foreach ($tagNames as $tagName) {
            if ($tagName == '') {
                continue;
            }

            /** @var Tag $existingTag */
            $existingTag = $tagsTable->find()
                ->where(['name' => $tagName])
                ->first();

            // Tag already exists
            if ($existingTag) {
                if ($existingTag->selectable) {
                    $this->tags[] = $existingTag;
                }

                continue;
            }

            // Tag should be created
            $newTag = $tagsTable->newEntity([
                'name' => $tagName,
                'user_id' => $this->user_id,
                'parent_id' => TagsTable::UNLISTED_GROUP_ID,
                'listed' => false,
                'selectable' => true,
            ]);
            if (!$tagsTable->save($newTag)) {
                throw new BadRequestException('There was an error adding the tag ' . $tagName);
            }
            $this->tags[] = $newTag;
        }

        // Remove duplicates
        $tagIds = [];
        foreach ($this->tags as $k => $tag) {
            if (in_array($tag->id, $tagIds)) {
                unset($this->tags[$k]);
                continue;
            }

            $tagIds[] = $tag->id;
        }
    }

    /**
     * Sets the location_slug property according to the value of the location property
     *
     * @return void
     */
    public function setLocationSlug()
    {
        $slug = str_replace('\'', '', $this->location);
        $slug = mb_strtolower($slug);
        $slug = Text::slug($slug);
        $this->location_slug = $slug;
    }

    /**
     * A virtual field that returns 'virtual' or 'physical' depending on whether the location name is 'Virtual Event'
     *
     * @return string
     */
    protected function _getLocationMedium()
    {
        if ($this->location == self::VIRTUAL_LOCATION) {
            return 'virtual';
        }

        return 'physical';
    }

    /**
     * A virtual field that returns this event's description in plain text, with HTML removed
     *
     * @return string
     */
    protected function _getDescriptionPlaintext()
    {
        $plaintext = $this->description;
        $plaintext = str_replace(['<br>', '<br />'], '\n', $plaintext);
        $plaintext = str_replace('</p>', '\n\n', $plaintext);
        $plaintext = strip_tags($plaintext);

        return trim($plaintext);
    }

    /**
     * Adds to $vcalendar a VTIMEZONE component for a Olson timezone identifier with daylight transitions covering the
     * given date range.
     *
     * Adapted from https://gist.github.com/thomascube/47ff7d530244c669825736b10877a200
     *
     * @param \Sabre\VObject\Component\VCalendar $vcalendar
     * @param string $tzid Timezone ID as used in PHP's Date functions
     * @param integer $from Unix timestamp with first date/time in this timezone
     * @param integer $to Unix timestap with last date/time in this timezone
     * @return \Sabre\VObject\Component\VCalendar|false VCalendar or false if no timezone information is available
     * @throws \Exception
     */
    public static function addVtimezone($vcalendar, $tzid, $from = 0, $to = 0)
    {
        if (!$from) $from = time();
        if (!$to)   $to = $from;

        try {
            $tz = new \DateTimeZone($tzid);
        }
        catch (\Exception $e) {
            return false;
        }

        // get all transitions for one year back/ahead
        $year = 86400 * 360;
        $transitions = $tz->getTransitions($from - $year, $to + $year);

        $vt = $vcalendar->createComponent('VTIMEZONE');
        $vt->TZID = $tz->getName();

        $std = null; $dst = null;
        foreach ($transitions as $i => $trans) {
            $cmp = null;

            // skip the first entry...
            if ($i == 0) {
                // ... but remember the offset for the next TZOFFSETFROM value
                $tzfrom = $trans['offset'] / 3600;
                continue;
            }

            // daylight saving time definition
            if ($trans['isdst']) {
                $t_dst = $trans['ts'];
                $dst = $vcalendar->createComponent('DAYLIGHT');
                $cmp = $dst;
            }
            // standard time definition
            else {
                $t_std = $trans['ts'];
                $std = $vcalendar->createComponent('STANDARD');
                $cmp = $std;
            }

            if ($cmp) {
                $dt = new DateTime($trans['time']);
                $offset = $trans['offset'] / 3600;

                $cmp->DTSTART = $dt->format('Ymd\THis') . 'Z';
                $cmp->TZOFFSETFROM = sprintf('%s%02d%02d', $tzfrom >= 0 ? '+' : '-', abs(floor($tzfrom)), ($tzfrom - floor($tzfrom)) * 60);
                $cmp->TZOFFSETTO   = sprintf('%s%02d%02d', $offset >= 0 ? '+' : '-', abs(floor($offset)), ($offset - floor($offset)) * 60);

                // add abbreviated timezone name if available
                if (!empty($trans['abbr'])) {
                    $cmp->TZNAME = $trans['abbr'];
                }

                $tzfrom = $offset;
                $vt->add($cmp);
            }

            // we covered the entire date range
            if ($std && $dst && min($t_std, $t_dst) < $from && max($t_std, $t_dst) > $to) {
                break;
            }
        }

        // add X-MICROSOFT-CDO-TZID if available
        $microsoftExchangeMap = array_flip(VObject\TimeZoneUtil::$microsoftExchangeMap);
        if (array_key_exists($tz->getName(), $microsoftExchangeMap)) {
            $vt->add('X-MICROSOFT-CDO-TZID', $microsoftExchangeMap[$tz->getName()]);
        }

        $vcalendar->add($vt);

        return $vcalendar;
    }
}
