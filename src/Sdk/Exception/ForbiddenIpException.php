<?php 

namespace Ipregistry\Sdk\Exception;

/**
 * Exception thrown when the request is forbidden due to IP filtering.
 */
class ForbiddenIpException extends IpregistryException
{
    /**
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
        $message = "The request was forbidden because your IP address is not allowed: " . $message;

        parent::__construct($message, $errorCode, $resolution, $code, $previous);
    }
}