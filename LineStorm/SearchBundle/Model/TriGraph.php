<?php

namespace LineStorm\SearchBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TriGraph
 *
 * @package LineStorm\SearchBundle\Model
 */
abstract class TriGraph
{
    /**
     * @var string
     */
    protected $tuple;

    /**
     * @param string $tuple
     */
    public function setTuple($tuple)
    {
        if(strlen($tuple) === 3)
        {
            $this->tuple = $tuple;
        }
    }

    /**
     * @return string
     */
    public function getTuple()
    {
        return $this->tuple;
    }
}
