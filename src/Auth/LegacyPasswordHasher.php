<?php
namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;
use Cake\Utility\Security;

class LegacyPasswordHasher extends AbstractPasswordHasher
{

    /**
     * Returns a hash of the provided password
     *
     * @param string $password Password
     * @return string
     */
    public function hash($password)
    {
        $salt = Security::getSalt();

        return sha1($salt . $password);
    }

    /**
     * Checks if the password matches the input for the provided hash
     *
     * @param string $password Password
     * @param string $hashedPassword The hashed version of the same password
     * @return bool
     */
    public function check($password, $hashedPassword): bool
    {
        return $this->hash($password) == $hashedPassword;
    }
}
