<?php 

namespace Ipregistry\Sdk\Exception;

/**
 * Exception thrown when too many ASNs are provided in a batch request.
 */
class TooManyAsnsException extends IpregistryException
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
        $message = "Too many ASNs were provided in the batch request: " . $message;

        parent::__construct($message, $errorCode, $resolution, $code, $previous);
    }
}