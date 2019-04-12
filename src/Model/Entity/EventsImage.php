<?php
namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * EventsImage Entity
 *
 * @property int $id
 * @property int $image_id
 * @property int $event_id
 * @property int $weight
 * @property string $caption
 * @property FrozenTime $created
 * @property FrozenTime $modified
 *
 * @property Image $image
 * @property Event $event
 */
class EventsImage extends Entity
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
        'image_id' => true,
        'event_id' => true,
        'weight' => true,
        'caption' => true,
        'created' => true,
        'modified' => true,
        'image' => true,
        'event' => true
    ];
}
