<?php
namespace App\Test\TestCase\Controller\Api\V1;

use App\Controller\ApiController;
use App\Test\TestCase\ApplicationTest;
use PHPUnit\Exception;

/**
 * Tests that the legacy `/v1/...` API URLs still resolve to the Api\V1 controllers after
 * the canonical URLs moved to `/api/v1/...`, and that legacy requests are flagged with
 * deprecation response headers.
 */
class LegacyRoutingTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Categories',
        'app.EventSeries',
        'app.Events',
        'app.EventsImages',
        'app.EventsTags',
        'app.Images',
        'app.Tags',
        'app.Users',
    ];

    /**
     * Tests that a legacy `/v1/...` URL resolves to the same controller as its `/api/v1/...`
     * equivalent and carries Deprecation/Sunset headers.
     *
     * @return void
     * @throws Exception
     */
    public function testLegacyUrlResolvesWithDeprecationHeaders()
    {
        $this->get('/v1/categories?apikey=' . $this->getApiKey());

        $this->assertResponseOk();
        $this->assertContentType('application/vnd.api+json');
        $this->assertHeader('Deprecation', 'true');
        $this->assertNotEmpty(
            $this->_response->getHeaderLine('Sunset'),
            'Legacy API responses should carry a Sunset header'
        );
        $this->assertStringContainsString(
            '/api/v1',
            $this->_response->getHeaderLine('Link'),
            'Legacy API responses should link to the successor version'
        );
    }

    /**
     * Tests that the current `/api/v1/...` URLs are not flagged as deprecated.
     *
     * @return void
     * @throws Exception
     */
    public function testCurrentUrlHasNoDeprecationHeaders()
    {
        $this->get('/api/v1/categories?apikey=' . $this->getApiKey());

        $this->assertResponseOk();
        $this->assertFalse(
            $this->_response->hasHeader('Deprecation'),
            'Current API URLs should not send a Deprecation header'
        );
        $this->assertFalse(
            $this->_response->hasHeader('Sunset'),
            'Current API URLs should not send a Sunset header'
        );
    }

    /**
     * Tests that a write request to a legacy URL behaves identically to the current URL and
     * is not bounced to the `/api/v1` equivalent with a redirect (which would drop the
     * request body for non-GET methods).
     *
     * @return void
     * @throws Exception
     */
    public function testLegacyWriteUrlIsNotVersionRedirected()
    {
        $url = '/event?apikey=' . $this->getApiKey();

        $this->post('/v1' . $url);
        $legacyStatus = $this->_response->getStatusCode();
        $legacyLocation = $this->_response->getHeaderLine('Location');

        $this->post('/api/v1' . $url);

        $this->assertSame(
            $this->_response->getStatusCode(),
            $legacyStatus,
            'Legacy and current write URLs should return the same status'
        );
        $this->assertStringNotContainsString(
            '/api/v1/event',
            $legacyLocation,
            'Legacy write URLs must resolve directly, not redirect to the /api/v1 URL'
        );
    }

    /**
     * Tests that the Sunset header is a valid RFC 7231 HTTP-date.
     *
     * @return void
     * @throws Exception
     */
    public function testSunsetHeaderIsHttpDate()
    {
        $this->get('/v1/categories?apikey=' . $this->getApiKey());

        $expected = gmdate(
            'D, d M Y H:i:s \G\M\T',
            (int)strtotime(ApiController::LEGACY_API_URL_SUNSET_DATE)
        );
        $this->assertSame($expected, $this->_response->getHeaderLine('Sunset'));
    }
}
