<?php
namespace App\Model\Entity;

use App\Auth\LegacyPasswordHasher;
use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $name
 * @property string $role
 * @property string $bio
 * @property string $email
 * @property string $password
 * @property int $mailing_list_id
 * @property int $facebook_id
 * @property string|null $api_key
 * @property string|null $token
 * @property string|null $reset_password_hash
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property EventSeries[] $event_series
 * @property Event[] $events
 * @property Image[] $images
 * @property Tag[] $tags
 */
class User extends Entity
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
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected array $_hidden = [
        'password',
    ];

    /**
     * Automatically hashes password
     *
     * @param string $password Password
     * @return bool|string
     */
    protected function _setPassword($password)
    {
        return (new LegacyPasswordHasher())->hash($password);
    }

    /**
     * Automatically trims and lowercases email
     *
     * @param string $email Email address
     * @return bool|string
     */
    protected function _setEmail($email)
    {
        $email = trim($email);
        $email = mb_strtolower($email);

        return $email;
    }

    /**
     * Generates a string token for the Users.api_key field
     *
     * The API key is used to authorize the user or application to make any API call
     *
     * @return string
     */
    public static function generateApiKey()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $length = 32;
        $apiKey = '';
        for ($i = 0; $i < $length; $i++) {
            $apiKey .= $characters[rand(0, $charactersLength - 1)];
        }

        return $apiKey;
    }

    /**
     * Generates a string token for the Users.token field
     *
     * The user token is used to authorize the API end-user to perform an action tied to record ownership, e.g.
     * adding events or updating user contact information
     *
     * @return string
     */
    public static function generateToken()
    {
        return self::generateApiKey();
    }

    /**
     * Returns a security code for a password reset for this user
     *
     * @return string
     */
    public function getResetPasswordHash()
    {
        $salt = Configure::read('password_reset_salt');
        $timezone = Configure::read('localTimezone');
        $month = (new \Cake\I18n\DateTime('now', $timezone))->format('my');

        return md5($this->id . $this->email . $salt . $month);
    }
}
