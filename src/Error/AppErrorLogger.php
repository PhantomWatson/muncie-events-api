<?php

namespace App\Error;

use App\Alert\ErrorAlert;
use Cake\Error\ErrorLoggerInterface;
use Cake\Error\PhpError;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Custom error logger that sends error information to Slack
 */
class AppErrorLogger extends \Cake\Error\ErrorLogger implements ErrorLoggerInterface
{
    /**
     * Log an error to Cake's Log subsystem and our custom Alert system
     *
     * @param \Cake\Error\PhpError $error The error to log
     * @param ?\Psr\Http\Message\ServerRequestInterface $request The request if in an HTTP context.
     * @param bool $includeTrace Should the log message include a stacktrace
     * @return void
     */
    public function logError(PhpError $error, ?ServerRequestInterface $request = null, bool $includeTrace = false): void
    {

        if (!$this->isDevelopmentEnvironment()) {
            $message = $error->getMessage();
            if ($request) {
                $message .= $this->getRequestContext($request);
            }
            if ($includeTrace) {
                $message .= "\nTrace:\n" . $error->getTraceAsString() . "\n";
            }
            ErrorAlert::send($message);
        }

        parent::logError($error, $request, $includeTrace);
    }

    private function isDevelopmentEnvironment(): bool
    {
        require_once(ROOT . DS . 'config' . DS . 'environment.php');
        $environment = getEnvironment();
        return $environment == 'development';
    }

    /**
     * Log an exception to Cake's Log subsystem and our custom Alert system
     *
     * @param \Throwable $exception The exception to log a message for.
     * @param \Psr\Http\Message\ServerRequestInterface|null $request The current request if available.
     * @param bool $includeTrace Whether or not a stack trace should be logged.
     * @return void
     */
    public function logException(
        Throwable $exception,
        ?ServerRequestInterface $request = null,
        bool $includeTrace = false
    ): void {
        if (!$this->isDevelopmentEnvironment()) {
            $message = $this->getMessage($exception, false, $includeTrace);
            if ($request !== null) {
                $message .= $this->getRequestContext($request);
            }
            ErrorAlert::send($message);
        }
        parent::logException($exception, $request, $includeTrace);
    }
}
