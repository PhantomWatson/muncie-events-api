<?php
/**
 * @var \App\View\AppView $this
 */
$years = (int)date('Y') - 2003;
?>
<h1 class="page_title">
    Help Out Muncie Events
</h1>

<p>
    For <?= $years ?> years, Muncie Events has been a labor of love, provided as a gift to the community by Munsonians
    donating untold thousands of hours of their time. And since many hands make light work, we're always looking for more
    volunteers to join the team so that this project can remain successful and sustainable.
</p>

<p>
    What kinds of volunteers, you ask?
</p>

<ul>
    <li>
        <strong>Event hunters</strong> - Our network of calendars can't operate without volunteers providing event
        information, so we're always in need of event hunters! The Muncie Events "staff", as it were, focuses on site
        maintenance and content moderation and rarely has the capacity for posting events
        on behalf of community groups, so these event-hunting volunteers are crucial for our success.
        Don't see your favorite venue or organization represented here? You can help fix that, and the community will
        be grateful for your efforts!
    </li>
    <li>
        <strong>Community liaisons</strong> - Volunteers can make a tremendous impact by connecting to local
        organizations and businesses to encourage them to submit their events and to put
        <?= $this->Html->link(
            'Muncie Events calendar widgets',
            [
                'controller' => 'Widgets',
                'action' => 'index',
            ]
        ) ?>
        on their websites.
    </li>
    <li>
        <strong>Copyeditors</strong> - It's important to us to have high-quality event information, and that involves
        hands-on copyediting for submitted content as well as management of our evolving
        <?= $this->Html->link('tagging system', ['controller' => 'tags', 'action' => 'index']) ?>.
    </li>
    <li>
        <strong>Content moderators</strong> - Our moderation model has one person assigned to each day of the week and
        getting automated notifications when new events get posted. These moderators not only review new submissions in
        order to filter out spam and inappropriate events, but also update submitted events to ensure that the
        information we publish is accurate, rich with useful details, and well-formatted.
    </li>
    <li>
        <strong>Software engineers</strong> - Developers of all skill levels can contribute to bugfixes,
        new feature development, modernization, and maintenance, either just for the sake of volunteering, to build up
        their résumé, or for credit toward a Computer Science degree. Additionally, there's a big opportunity to help
        modernize our mobile application and find creative new ways to use <?= $this->Html->link(
            'the Muncie Events API',
            [
                'controller' => 'Pages',
                'action' => 'api',
            ]
        ) ?>, such as automating the posting and publishing of events.
    </li>
</ul>

<p>
    If you're interested in helping out in any capacity, reach out via
    <a href="mailto:admin@muncieevents.com">admin@muncieevents.com</a>
    or shoot a message to <a href="https://www.facebook.com/MuncieEvents/">the Muncie Events Facebook page</a> or
    <a href="https://bsky.app/profile/muncieevents.bsky.social">BlueSky profile</a>.
</p>
