<?php
namespace App\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * MailingList Entity
 *
 * @property int $id
 * @property string $email
 * @property bool $all_categories
 * @property bool $weekly
 * @property bool $daily_sun
 * @property bool $daily_mon
 * @property bool $daily_tue
 * @property bool $daily_wed
 * @property bool $daily_thu
 * @property bool $daily_fri
 * @property bool $daily_sat
 * @property bool $new_subscriber
 * @property FrozenTime $created
 * @property FrozenTime $modified
 * @property FrozenTime|null $processed_daily
 * @property FrozenTime|null $processed_weekly
 *
 * @property User[] $users
 * @property Category[] $categories
 */
class MailingList extends Entity
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
        'email' => true,
        'all_categories' => true,
        'weekly' => true,
        'daily_sun' => true,
        'daily_mon' => true,
        'daily_tue' => true,
        'daily_wed' => true,
        'daily_thu' => true,
        'daily_fri' => true,
        'daily_sat' => true,
        'new_subscriber' => true,
        'created' => true,
        'modified' => true,
        'processed_daily' => true,
        'processed_weekly' => true,
        'users' => true,
        'categories' => true
    ];
}
