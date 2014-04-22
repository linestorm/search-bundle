<?php

namespace LineStorm\SearchBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class FullText
 *
 * @package LineStorm\SearchBundle\Model
 */
abstract class FullText
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }


}
