<?php 

namespace Ipregistry\Sdk\Exception;

use Exception;

/**
 * Base exception class for the Ipregistry SDK.
 */
class IpregistryException extends Exception
{
    /** @var string|null The error code from the Ipregistry API. */
    protected $errorCode;

    /** @var string|null The resolution suggestion from the Ipregistry API. */
    protected $resolution;

    /**
     * IpregistryException constructor.
     *
     * @param string $message The exception message.
     * @param string|null $errorCode The error code from the API.
     * @param string|null $resolution The resolution suggestion from the API.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previous exception.
     */
    public function __construct(
        string $message = "", 
        ?string $errorCode = null, 
        ?string $resolution = null, 
        int $code = 0, 
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->resolution = $resolution;
    }

    /**
     * Get the error code from the API.
     *
     * @return string|null The error code, or null if not available.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get the resolution suggestion from the API.
     *
     * @return string|null The resolution suggestion, or null if not available.
     */
    public function getResolution(): ?string
    {
        return $this->resolution;
    }
}