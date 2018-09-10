<?php

namespace Electroneum\Vendor\Exception;

use Exception;

class VendorException extends Exception
{
    // Ensure the message is required.
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        // Assigned parameters.
        parent::__construct($message, $code, $previous);
    }

    // Return a string representation of the object.
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
