<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

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
 * @property \App\Model\Entity\EventSeries $series
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
        'series' => true,
        'images' => true,
        'tags' => true
    ];
}
