<?php
// The contents of this page are returned by the API endpoint /pages/about
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

$eventsTable = TableRegistry::getTableLocator()->get('Events');
$eventCount = $eventsTable->find()->count();
$timezone = Configure::read('localTimezone');
$yearsCount = (int)(new FrozenTime('now', $timezone))->format('Y') - 2009;
?>

<h1 class="page_title">
    About Muncie Events
</h1>

<p>
    Muncie Events is a free, comprehensive event promotion service provided to the city of Muncie, Indiana.
    It strives to
    make learning about and promoting events easier for everyone by providing the underlying service to empower local
    websites and mobile apps to be part of the same event promotion network. Event information collected by Muncie
    Events gets distributed
</p>
<ul>
    <li>
        to every website displaying
        <a href="https://muncieevents.com/widgets">a Muncie Events calendar</a>
    </li>
    <li>
        to apps using
        <a href="<?= Router::url([
            'controller' => 'Pages',
            'action' => 'api',
        ], true) ?>">the Muncie Events API</a>
    </li>
    <li>
        to
        <?= $this->Html->link(
            'a customizable mailing list',
            [
                'controller' => 'MailingList',
                'action' => 'index',
            ]
        ) ?>
    </li>
    <li>
        to an Android App (temporarily unavailable to download)
    </li>
    <li>
        and to <?= $this->Html->link(
            'customizable Google Calendar feeds',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'feeds',
            ]
        ) ?>
    </li>
</ul>

<p>
    Muncie Events began in 2003 as a component of the community website TheMuncieScene.com. Its event database currently
    holds information for <?= number_format($eventCount) ?> local events submitted by hundreds of people over
    the past <?= $yearsCount ?> years.
    Like us on <a href="https://www.facebook.com/MuncieEvents/">facebook.com/MuncieEvents</a> for news about this
    service and announcements about new features being added.
</p>

<?php
    $credits = [
        'People' => [
            '<a href="mailto:graham@phantomwatson.com">Graham Watson</a>' => 'Web Developer, Administrator',
            'Erica Dee Fox' => 'Web Developer',
            'Gunner Bills' => 'Mobile App Developer',
            'Michael Bratton' => 'Mobile App Developer',
            'Ronan Furlong' => 'Mobile App Developer',
            'Timothy Hartke' => 'Mobile App Developer',
            'Matthew Taylor' => 'Mobile App Developer',
            'Sydnee Kuebler' => 'Icon Designer',
            'Benjamin Easley' => 'Graphic Designer',
            'Nicholas Boyum' => 'Artist (map of Muncie background image)',
        ],
        'Software' => [
            '<a href="http://cakephp.org">CakePHP</a>' => 'Back-end framework',
            '<a href="http://jquery.com/">jQuery</a> &amp; <a href="http://jqueryui.com/">jQuery UI</a>' => 'Front-end framework',
            '<a href="https://facebook.github.io/react-native/">React Native</a>' => 'Mobile app framework',
            '<a href="http://dimsemenov.com/plugins/magnific-popup/">Magnific Popup</a>' => 'Elegant media popups',
            '<a href="http://recaptcha.net/">reCAPTCHA</a>' => 'Spam defense',
        ],
    ];
?>

<ul id="credits">
    <?php foreach ($credits as $category => $members) : ?>
        <li class="category">
            <?= $category ?>
            <br class="break" />
        </li>
        <?php foreach ($members as $name => $position) : ?>
            <li class="row">
                <div class="name"><?= $name ?></div>
                <div class="position"><?= $position ?></div>
                <br class="break" />
            </li>
        <?php endforeach; ?>
        <li class="row" style="border: none;">&nbsp;</li>
    <?php endforeach; ?>
</ul>
