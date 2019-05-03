<?php
// The contents of this page are returned by the API endpoint /pages/about
use Cake\ORM\TableRegistry;
$eventsTable = TableRegistry::getTableLocator()->get('Events');
$eventCount = $eventsTable->find()->count();
$yearsCount = date('Y') - 2009;
?>
<h1 class="page_title">
    About Muncie Events
</h1>

<p>
    Muncie Events is a free, comprehensive event promotion service provided to the city of
    Muncie, Indiana with the support of Ball State University's <a href="https://bsu.edu/cber">Center for Business and
    Economic Research</a> and the <a href="https://munciearts.org">Muncie Arts and Culture Council</a>. It strives to
    make learning about and promoting events easier for everyone by providing the underlying service to empower local
    websites and mobile apps to be part of the same event promotion network. Event information collected by Muncie
    Events gets distributed to every website displaying
    <a href="https://muncieevents.com/widgets">a Muncie Events calendar</a>, to apps using
    <a href="https://api.muncieevents.com">the Muncie Events API</a>, and to
    <a href="https://muncieevents.com/mailing_list/join">a customizable mailing list</a>.
</p>

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
            'Sydnee Kuebler' => 'Icon Designer',
            'Benjamin Easley' => 'Graphic Designer',
            'Nicholas Boyum' => 'Artist (map of Muncie background image)',
        ],
        'Organizations' => [
            '<a href="https://munciearts.org">Muncie Arts and Culture Council</a>' => '',
            '<a href="http://bsu.edu/cber">Center for Business and Economic Research</a>' => ''
        ],
        'Software' => [
            '<a href="http://cakephp.org">CakePHP</a>' => 'Back-end framework',
            '<a href="http://jquery.com/">jQuery</a> &amp; <a href="http://jqueryui.com/">jQuery UI</a>' => 'Front-end framework',
            '<a href="https://facebook.github.io/react-native/">React Native</a>' => 'Mobile app framework',
            '<a href="http://dimsemenov.com/plugins/magnific-popup/">Magnific Popup</a>' => 'Elegant media popups',
            '<a href="http://www.digitalmagicpro.com/jPicker/">jPicker</a>' => 'Color picker',
            '<a href="http://recaptcha.net/">reCAPTCHA</a>' => 'Spam defense',
        ]
    ];
?>

<ul id="credits">
    <?php foreach ($credits as $category => $members): ?>
        <li class="category">
            <?= $category ?>
            <br class="break" />
        </li>
        <?php foreach ($members as $name => $position): ?>
            <li class="row">
                <div class="name"><?= $name ?></div>
                <div class="position"><?= $position ?></div>
                <br class="break" />
            </li>
        <?php endforeach; ?>
        <li class="row" style="border: none;">&nbsp;</li>
    <?php endforeach; ?>
</ul>

