<?php
/**
 * @var \App\View\AppView $this
 * @var string $email
 * @var string $resetUrl
 */
use Cake\Core\Configure;

$timezone = Configure::read('localTimezone');
?>
<h1>
    <a href="https://muncieevents.com">
        <img src="https://muncieevents.com/img/email_logo.png" alt="Muncie Events" />
    </a>
</h1>

<p>
    <?= $email ?>,
</p>

<p>
    Someone (presumably you) just requested that your password for MuncieEvents.com be reset
    so you can log in again. If you click on this link, you'll be prompted to enter in a new password to overwrite
    your old one.
</p>

<p>
    <a href="<?= $resetUrl ?>">
        <?= $resetUrl ?>
    </a>
</p>

<p>
    <strong>NOTE: This link will expire in 24 hours.</strong>
    If you need to reset your password after that, you'll need to request another password reset link.
</p>

<p>
    <a href="https://MuncieEvents.com">Muncie Events</a>
</p>
