<?php

namespace Gloubster\Documents;



/**
 * Gloubster\Documents\Specification
 */
class Specification
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var boolean $done
     */
    protected $done = false;


    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Specification
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set done
     *
     * @param boolean $done
     * @return Specification
     */
    public function setDone($done)
    {
        $this->done = $done;
        return $this;
    }

    /**
     * Get done
     *
     * @return boolean $done
     */
    public function getDone()
    {
        return $this->done;
    }
    /**
     * @var Gloubster\Documents\Parameter
     */
    protected $parameters = array();

    public function __construct()
    {
        $this->parameters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add parameters
     *
     * @param Gloubster\Documents\Parameter $parameters
     */
    public function addParameters(\Gloubster\Documents\Parameter $parameters)
    {
        $this->parameters[] = $parameters;
    }

    /**
     * Get parameters
     *
     * @return Doctrine\Common\Collections\Collection $parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
