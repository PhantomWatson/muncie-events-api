<?php
declare(strict_types=1);

namespace App\Authenticator;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Identifier\TokenIdentifier;
use Psr\Http\Message\ServerRequestInterface;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private array $apiKeyFields = ['apikey', 'apiKey'];

    /**
     * Authenticate a user based on an API key query parameter.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object.
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $apiKey = $this->getApiKey($request);
        if (empty($apiKey)) {
            return new Result(null, Result::FAILURE_CREDENTIALS_MISSING);
        }

        $identity = $this->getIdentifier()->identify([
            TokenIdentifier::CREDENTIAL_TOKEN => $apiKey,
        ]);

        if (empty($identity)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, ['API key not recognized']);
        }

        return new Result($identity, Result::SUCCESS);
    }

    /**
     * Returns the API key from the request query string, or null if not present.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object.
     * @return string|null
     */
    private function getApiKey(ServerRequestInterface $request): ?string
    {
        foreach ($this->apiKeyFields as $field) {
            $apiKey = $request->getQueryParams()[$field] ?? null;
            if ($apiKey) {
                return $apiKey;
            }
        }

        return null;
    }
}
