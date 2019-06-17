<?php
namespace App\Model\Entity;

use App\Model\Table\TagsTable;
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
        'tags'
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
     * Returns a full datetime with correct UTC offset representing the provided time
     *
     * Example: Events taking place at 10am in Indiana are stored as "10:00:00", which is has no timezone info and may
     * be assumed incorrectly to be UTC. This method returns a full RFC 3339 string with the correct timezone offset,
     * year, month, and day, e.g. "2019-12-06T10:00:00+05:00".
     *
     * @param FrozenDate $date Date object
     * @param FrozenTime|null $localTime Time object
     * @return string|null
     * @throws Exception
     */
    public static function getDatetime($date, $localTime)
    {
        if (!$localTime) {
            return null;
        }

        $timeString = $localTime->toDateTimeString();
        $correctedTime = new Time($timeString);
        $correctedTime->timezone(self::TIMEZONE);

        // Fix missing date info
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

        return $correctedTime->toRfc3339String();
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
     * Adds tag entities to this event entity, creating new records if necessary
     *
     * @param int[] $tagIds An array of tag IDs
     * @param string|string[] $tagNames An array of tag names or a comma-delimited string
     * @return void
     * @throws BadRequestException
     */
    public function processTags($tagIds, $tagNames)
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $this->tags = $this->tags ?? [];
        foreach ($tagIds as $tagId) {
            $this->tags[] = $tagsTable->get($tagId);
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
                'selectable' => true
            ]);
            if (!$tagsTable->save($newTag)) {
                throw new BadRequestException('There was an error adding the tag ' . $tagName);
            }
            $this->tags[] = $newTag;
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
}
