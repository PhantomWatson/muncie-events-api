<?php
/**
 * @var \App\View\AppView $this
 * @var string $email
 * @var string $resetUrl
 */
use Cake\Core\Configure;

$timezone = Configure::read('localTimezone');
?>
<?= $email ?>,

Someone (presumably you) just requested that your password for MuncieEvents.com be reset so you can log in again.
If you go to the following URL, you'll be prompted to enter in a new password to overwrite your old one.

<?= $resetUrl ?>


NOTE: This link will expire in 24 hours. If you need to reset your password after
that, you'll need to request another password reset link.


Muncie Events
https://MuncieEvents.com
