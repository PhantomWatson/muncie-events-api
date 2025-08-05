<?php
use Cake\Core\Configure;

$adminEmail = Configure::read('adminEmail');
?>
<ul>
    <li>
        <strong>Muncie Events:</strong> Only events taking place in Muncie may be posted.
    </li>
    <li>
        <strong>Events Only:</strong> In general, advertisements for services offered by businesses
        (such as sales) and deadlines are not considered events and may not be posted. A display of art per se does
        not generally count as an event, but the opening day of an art exhibition is very welcome to be posted, as
        would be any days in which a performance is taking place, such as a presentation by an artist.
    </li>
    <li>
        <strong>Public Events:</strong> Only events open to the general public (not counting age restrictions) and
        relevant to the general public may be posted. Private events, members-only events, or events that only pertain
        to specific group are disallowed. Events that can only be attended by people who attended previous events fall
        under "members only", and as such, only the first meeting of multiple-day classes may be posted if subsequent
        meetings are restricted to only enrolled students.
    </li>
    <li>
        <strong>Virtual Events:</strong> Virtual events may be posted if they are live video broadcasts being made from
        a location in Muncie or virtual meetings hosted by Muncie groups that are open to the public to attend. Live
        broadcasts should be performances, educational presentations, or otherwise beneficial and interesting to the
        general public. Streams of a personal nature, gaming streams, and streams which are primarily for a non-local
        audience are not a great fit for promotion through Muncie Events.
    </li>
    <li>
        <strong>Significant Events:</strong> Ongoing events that take place very frequently (such as
        unspecific live music at a coffee shop every day) may not be posted.
    </li>
    <li>
        <strong>One Post Per Event:</strong> Duplicate entries for the same event are not allowed. If
        an event has been posted multiple times, all postings after the first will be removed. At the
        administrators' discretion, the posting with more complete, detailed, and/or accurate
        information may be retained, even if it was not posted first.
    </li>
    <li>
        <strong>Language:</strong> To be considerate of our wide and diverse audience, offensive language
        is not allowed. In general, any words that would be censored on television are not allowed in
        posted events.
    </li>
    <li>
        <strong>Adult-Oriented Events</strong>: Any events of a sexual nature or with any other content
        inappropriate for an audience under 18 years of age must include the 'adult-oriented' tag so
        that it can be filtered out by users requesting only events appropriate for all ages.
    </li>
    <li>
        <strong>Formatting</strong>: An event's title should not redundantly include
        information entered into other fields, such as location name, date, address, cost.
        Using CAPS LOCK FOR EMPHASIS and other unpleasantly-formatted text is not allowed.
    </li>
    <li>
        <strong>Questions?</strong> Please email <a href="mailto:<?= $adminEmail ?>"><?= $adminEmail ?></a> if you have
        any questions.
    </li>
</ul>
