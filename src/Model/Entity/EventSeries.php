<?php
namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * EventSeries Entity
 *
 * @property int $id
 * @property string $title
 * @property int $user_id
 * @property bool $published
 * @property FrozenTime $created
 * @property FrozenTime $modified
 *
 * @property User $user
 * @property Event[] $events
 * @property Event[] $pastEvents
 * @property Event[] $upcomingEvents
 */
class EventSeries extends Entity
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
        'user_id' => true,
        'published' => true,
        'created' => true,
        'modified' => true,
        'user' => true
    ];

    /**
     * Reads the property `events` and sets the properties `pastEvents` and `upcomingEvents`
     *
     * @return void
     */
    public function splitEventsPastFuture()
    {
        $this->upcomingEvents = [];
        $this->pastEvents = [];
        if (!$this->events) {
            return;
        }

        foreach ($this->events as $event) {
            $property = $event->date->format('Y-m-d') < date('Y-m-d')
                ? 'pastEvents'
                : 'upcomingEvents';
            $this->$property[] = $event;
        }
        rsort($this->pastEvents);
    }
}
