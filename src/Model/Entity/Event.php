<?php
namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use DateTime;

/**
 * Event Entity
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $location
 * @property string $location_details
 * @property string $address
 * @property int $user_id
 * @property int $category_id
 * @property int $series_id
 * @property \Cake\I18n\FrozenDate $date
 * @property \Cake\I18n\FrozenTime $time_start
 * @property \Cake\I18n\FrozenTime $time_end
 * @property string $age_restriction
 * @property string $cost
 * @property string $source
 * @property bool $published
 * @property int $approved_by
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\EventSeries $event_series
 * @property \App\Model\Entity\Image[] $images
 * @property \App\Model\Entity\Tag[] $tags
 */
class Event extends Entity
{

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
        'tags' => true
    ];

    /**
     * Transforms the time into a full datetime object with correct UTC offset
     *
     * @return string
     */
    protected function _getTimeStart()
    {
        return $this->getCorrectedTime($this->_properties['time_start']);
    }

    /**
     * Transforms the time into a full datetime object with correct UTC offset
     *
     * @return string
     */
    protected function _getTimeEnd()
    {
        return $this->getCorrectedTime($this->_properties['time_end']);
    }

    /**
     * Returns a datetime, corrected for being a local time value stored as UTC
     *
     * Example: Events taking place at 10am in Indiana are stored as "10:00:00", which is interpreted
     * as "10:00:00+00:00", i.e. 10am in UTC. This returns such a time as "10:00:00+05:00" so that it's
     * correctly interpreted and adds the correct year, month, and day information so a full RFC 3339
     * string can be outputted.
     *
     * @param FrozenTime|null $localTime Indiana time mistakenly represented as UTC
     * @return string|null
     */
    private function getCorrectedTime($localTime)
    {
        if (!$localTime) {
            return null;
        }

        $timeString = $localTime->toDateTimeString();
        $correctedTime = new Time($timeString);
        $timezone = 'America/Indiana/Indianapolis';
        $correctedTime->timezone($timezone);

        // Fix missing date info
        $correctedTime
            ->year($this->_properties['date']->year)
            ->month($this->_properties['date']->month)
            ->day($this->_properties['date']->day);

        // Change from Indiana time to UTC time
        $offset = timezone_offset_get(timezone_open($timezone), new DateTime($localTime));
        $isNegOffset = stripos($offset, '-') === 0;
        $modification = str_replace(
            $isNegOffset ? '-' : '+',
            $isNegOffset ? '+' : '-',
            $offset
        ) . ' seconds';
        $correctedTime->modify($modification);

        return $correctedTime->toRfc3339String();
    }
}
