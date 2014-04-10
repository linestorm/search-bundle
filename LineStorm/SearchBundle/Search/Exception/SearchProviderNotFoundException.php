<?php

namespace LineStorm\SearchBundle\Search\Exception;

use Exception;

/**
 * Class SearchProviderNotFoundException
 *
 * @package LineStorm\SearchBundle\Search\Exception
 */
class SearchProviderNotFoundException extends \Exception
{
    /**
     * @param string    $message
     * @param Exception $previous
     */
    public function __construct($message = "", Exception $previous = null)
    {
        parent::__construct("Search Provider {$message} does not exist", null, $previous);
    }
} 
