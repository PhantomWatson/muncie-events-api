<?php
namespace App\Model\Entity;

use App\Model\Table\TagsTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenTime;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
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
 * @property \Cake\I18n\FrozenTime|null $time_end
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
    const TIMEZONE = 'America/Indiana/Indianapolis';

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
     * Takes an array of image data and sets proper join data for the next save operation
     *
     * @param array $imagesData Array of ['id' => $imageId, 'caption' => ...] arrays
     * @return void
     */
    public function setImageJoinData($imagesData)
    {
        $this->images = [];
        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        foreach ($imagesData as $i => $imageData) {
            $image = $imagesTable->get($imageData['id']);
            $image->_joinData = new Entity([
                'weight' => $i + 1,
                'caption' => $imageData['caption']
            ]);
            $this->images[] = $image;
        }
    }

    /**
     * Transforms the time into a full datetime object with correct UTC offset
     *
     * @return string
     * @throws \Exception
     */
    protected function _getTimeStart()
    {
        return $this->getCorrectedTime($this->_properties['time_start']);
    }

    /**
     * Transforms the time into a full datetime object with correct UTC offset
     *
     * @return string
     * @throws \Exception
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
     * @throws \Exception
     */
    private function getCorrectedTime($localTime)
    {
        if (!$localTime) {
            return null;
        }

        $timeString = $localTime->toDateTimeString();
        $correctedTime = new Time($timeString);
        $correctedTime->timezone(self::TIMEZONE);

        // Fix missing date info
        $correctedTime
            ->year($this->_properties['date']->year)
            ->month($this->_properties['date']->month)
            ->day($this->_properties['date']->day);

        // Change from Indiana time to UTC time
        $offset = timezone_offset_get(timezone_open(self::TIMEZONE), new DateTime($localTime));
        $isNegOffset = stripos($offset, '-') === 0;
        $modification = str_replace(
            $isNegOffset ? '-' : '+',
            $isNegOffset ? '+' : '-',
            $offset
        ) . ' seconds';
        $correctedTime->modify($modification);

        return $correctedTime->toRfc3339String();
    }

    /**
     * Sets the event to approved if $user (the user submitting the form) is an administrator
     *
     * @param array|User $user The user submitting the form (not necessarily the original event author)
     * @return void
     * @throws InternalErrorException
     */
    public function autoApprove($user)
    {
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
     * Sets the event to approved and published if $user (the user submitting the form) is an administrator
     *
     * @param array|User $user The user submitting the form (not necessarily the original event author)
     * @return void
     * @throws InternalErrorException
     */
    public function autoPublish($user)
    {
        if ($this->userIsAutoPublishable($user)) {
            $this->published = true;
        }
    }

    /**
     * Returns TRUE if the specified user should have their events automatically published
     *
     * @param array|User $user Array of user record info, empty for anonymous users
     * @return bool
     */
    public function userIsAutoPublishable($user)
    {
        if (!is_array($user)) {
            $user = $user->toArray();
        }
        if (!isset($user['id'])) {
            return false;
        }

        if (isset($user['role']) && $user['role'] == 'admin') {
            true;
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
            'user_id' => $userId
        ]);
    }

    /**
     * Takes a list of tags, adds tags if they don't already exist, and adds them to this event entity
     *
     * @param string|array $tagNames An array of tag names or a comma-delimited string
     * @return void
     * @throws BadRequestException
     */
    public function processCustomTags($tagNames)
    {
        if (!is_array($tagNames)) {
            $tagNames = explode(',', $tagNames);
        }
        $tagNames = array_map('trim', $tagNames);
        $tagNames = array_map('strtolower', $tagNames);
        $tagNames = array_unique($tagNames);

        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        foreach ($tagNames as $tagName) {
            if ($tagName == '') {
                continue;
            }

            /** @var Tag $existingTag */
            $existingTag = $tagsTable->find()
                ->select(['id', 'selectable'])
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
                'selectable' => true
            ]);
            if (!$tagsTable->save($newTag)) {
                throw new BadRequestException('There was an error adding the tag ' . $tagName);
            }
            $this->tags[] = $newTag;
        }
    }
}
