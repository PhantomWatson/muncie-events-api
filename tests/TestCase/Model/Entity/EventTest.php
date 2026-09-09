<?php

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Event;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Event Test Case
 */
class EventTest extends TestCase
{
    /**
     * Returns an Event with the given description
     *
     * @param string|null $description Description
     * @return \App\Model\Entity\Event
     */
    private function eventWithDescription(?string $description): Event
    {
        return new Event(['description' => $description]);
    }

    /**
     * Tests that description_autolinked returns NULL for an empty description
     *
     * @return void
     */
    public function testDescriptionAutolinkedEmpty(): void
    {
        $this->assertNull($this->eventWithDescription(null)->description_autolinked);
        $this->assertSame('', $this->eventWithDescription('')->description_autolinked);
    }

    /**
     * Tests that description_autolinked links plain URLs and email addresses
     *
     * @return void
     */
    public function testDescriptionAutolinkedLinksPlainUrlsAndEmails(): void
    {
        $event = $this->eventWithDescription('Tickets at https://example.com/show or email info@example.com now');
        $expected = 'Tickets at <a href="https://example.com/show">https://example.com/show</a> or email '
            . '<a href="mailto:info@example.com">info@example.com</a> now';
        $this->assertSame($expected, $event->description_autolinked);
    }

    /**
     * Tests that a bare "www." host is linked with an added http:// scheme
     *
     * @return void
     */
    public function testDescriptionAutolinkedLinksBareWwwHost(): void
    {
        $event = $this->eventWithDescription('See www.muncieevents.com for details');
        $expected = 'See <a href="http://www.muncieevents.com">www.muncieevents.com</a> for details';
        $this->assertSame($expected, $event->description_autolinked);
    }

    /**
     * Tests that a URL that is already wrapped in an anchor tag is left untouched
     *
     * @return void
     */
    public function testDescriptionAutolinkedIgnoresExistingLinks(): void
    {
        $html = 'Buy at <a href="https://example.com/show">https://example.com/show</a> today';
        $this->assertSame($html, $this->eventWithDescription($html)->description_autolinked);
    }

    /**
     * Tests that a URL-like substring inside the href of a manually-created link is not wrapped in a nested anchor
     *
     * This is the case that a plain TextHelper::autoLinkUrls() call gets wrong, producing invalid HTML.
     *
     * @return void
     */
    public function testDescriptionAutolinkedIgnoresUrlSubstringInsideHref(): void
    {
        $html = 'Presale <a href="https://example.com/v3/__https:/www.example.org/'
            . 'the-songwriter-sessions/february-2026__;!!NHHyjs">here</a> and email bob@band.org';
        $expected = 'Presale <a href="https://example.com/v3/__https:/www.example.org/'
            . 'the-songwriter-sessions/february-2026__;!!NHHyjs">here</a> and email '
            . '<a href="mailto:bob@band.org">bob@band.org</a>';
        $this->assertSame($expected, $this->eventWithDescription($html)->description_autolinked);
    }

    /**
     * Tests that a URL abutting a non-space character (other than a string boundary) is not linked
     *
     * @return void
     */
    public function testDescriptionAutolinkedRequiresWhitespaceOrBoundary(): void
    {
        $event = $this->eventWithDescription('Wrapped(https://example.com)here');
        $this->assertSame('Wrapped(https://example.com)here', $event->description_autolinked);
    }

    /**
     * Tests that ampersands in a linked URL are HTML-encoded in both the href and the label
     *
     * @return void
     */
    public function testDescriptionAutolinkedEscapesUrl(): void
    {
        $event = $this->eventWithDescription('Search https://example.com/?a=1&b=2 today');
        $expected = 'Search <a href="https://example.com/?a=1&amp;b=2">https://example.com/?a=1&amp;b=2</a> today';
        $this->assertSame($expected, $event->description_autolinked);
    }
}
