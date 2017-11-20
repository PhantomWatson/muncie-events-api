<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EventsImage Entity
 *
 * @property int $id
 * @property int $image_id
 * @property int $event_id
 * @property int $weight
 * @property string $caption
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Image $image
 * @property \App\Model\Entity\Event $event
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
