<?php

namespace LineStorm\SearchBundle\Search\Exception;

/**
 * Class EntityNotSupportedException
 *
 * @package LineStorm\SearchBundle\Search\Exception
 */
class EntityNotSupportedException extends \Exception
{
    /**
     * @var array|object
     */
    private $entities;

    /**
     * @param object|array $entities
     * @param string       $message
     * @param int          $code
     * @param \Exception   $previous
     */
    public function __construct($entities, $message = "Entities not supported", $code = 0, \Exception $previous = null)
    {
        $this->entities = $entities;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return object|array
     */
    public function getEntities()
    {
        return $this->entities;
    }


} 
