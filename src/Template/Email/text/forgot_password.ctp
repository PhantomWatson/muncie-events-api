<?php
/**
 * @var AppView $this
 * @var string $email
 * @var string $resetUrl
 */
use App\View\AppView;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$timezone = Configure::read('localTimezone');
?>
<?= $email ?>,

Someone (presumably you) just requested that your password for MuncieEvents.com be reset so you can log in again.
If you go to the following URL, you'll be prompted to enter in a new password to overwrite your old one.

<?= $resetUrl ?>


NOTE: That link will only work for the rest of <?= (new FrozenTime('now', $timezone))->format('F Y') ?>. If you need to reset your password after
that, you'll need to request another password reset link.


Muncie Events
https://MuncieEvents.com
