<?php
namespace App\View\Helper;

use App\Model\Entity\Event;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\View\Helper;

/**
 * Class JsonLdHelper
 *
 * Builds schema.org JSON-LD structured data for consumption by Google (rich results / Events search feature)
 *
 * @package App\View\Helper
 */
class JsonLdHelper extends Helper
{
    /**
     * Returns a schema.org Event array built from an Event entity
     *
     * @param \App\Model\Entity\Event $event Event entity
     * @return array
     */
    public function event(Event $event): array
    {
        $url = Router::url(
            [
                'controller' => 'Events',
                'action' => 'view',
                'id' => $event->id,
            ],
            true
        );

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title,
            'startDate' => $event->start_datetime_iso8601,
            'eventStatus' => 'https://schema.org/EventScheduled',
            'url' => $url,
        ];

        if ($event->description_plaintext) {
            $data['description'] = $event->description_plaintext;
        }

        $data['endDate'] = $event->end_datetime_iso8601 ?: $event->start_datetime_iso8601;

        if ($event->location_medium === 'virtual') {
            $data['eventAttendanceMode'] = 'https://schema.org/OnlineEventAttendanceMode';
            $data['location'] = [
                '@type' => 'VirtualLocation',
                'url' => $event->address ?: $url,
            ];
        } else {
            $data['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
            $data['location'] = [
                '@type' => 'Place',
                'name' => $event->location,
                'address' => $event->address ?: $event->location,
            ];
        }

        $images = $this->images($event);
        if ($images) {
            $data['image'] = $images;
        }

        if (!empty($event->user->name)) {
            // Individual users, not organizations, submit events on this site
            $data['organizer'] = [
                '@type' => 'Person',
                'name' => $event->user->name,
            ];
        }

        $offers = $this->offers($event, $url);
        if ($offers) {
            $data['offers'] = $offers;
        }

        return $data;
    }

    /**
     * Returns full-size image URLs for an event's images
     *
     * @param \App\Model\Entity\Event $event Event entity
     * @return string[]
     */
    private function images(Event $event): array
    {
        $baseUrl = Configure::read('eventImageBaseUrl');
        $images = [];
        foreach ($event->images as $image) {
            $images[] = $baseUrl . 'full/' . $image->filename;
        }

        return $images;
    }

    /**
     * Returns a schema.org Offer array if the event's free-text cost field can be confidently
     * parsed as a single numeric price, or NULL otherwise
     *
     * @param \App\Model\Entity\Event $event Event entity
     * @param string $url Canonical event URL, used as the Offer URL
     * @return array|null
     */
    private function offers(Event $event, string $url): ?array
    {
        $cost = trim((string)$event->cost);
        if ($cost === '') {
            return null;
        }

        if (in_array(strtolower($cost), ['free', 'no charge', 'no cost'], true)) {
            $price = '0';
        } elseif (preg_match('/^\$?(\d+(?:\.\d{1,2})?)$/', $cost, $matches)) {
            $price = $matches[1];
        } else {
            return null;
        }

        return [
            '@type' => 'Offer',
            'price' => $price,
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'url' => $url,
        ];
    }

    /**
     * Wraps a data array in a JSON-LD script tag, safe for embedding in HTML
     *
     * @param array $data Structured data array
     * @return string
     */
    public function script(array $data): string
    {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
        );

        return sprintf('<script type="application/ld+json">%s</script>', $json);
    }
}
