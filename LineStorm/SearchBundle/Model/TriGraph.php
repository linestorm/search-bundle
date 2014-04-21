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
    protected $triplet;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @param string $triplet
     */
    public function setTriplet($triplet)
    {
        $this->triplet = $triplet;
    }

    /**
     * @return string
     */
    public function getTriplet()
    {
        return $this->triplet;
    }

    /**
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }


}
