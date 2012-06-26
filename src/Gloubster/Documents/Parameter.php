<?php

namespace Gloubster\Documents;



/**
 * Gloubster\Documents\Parameter
 */
class Parameter
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
     * @var string $value
     */
    protected $value;

    /**
     * @var Gloubster\Documents\Specification
     */
    protected $specifications = array();

    public function __construct()
    {
        $this->specifications = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * @return Parameter
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
     * Set value
     *
     * @param string $value
     * @return Parameter
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return string $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Add specifications
     *
     * @param Gloubster\Documents\Specification $specifications
     */
    public function addSpecifications(\Gloubster\Documents\Specification $specifications)
    {
        $this->specifications[] = $specifications;
    }

    /**
     * Get specifications
     *
     * @return Doctrine\Common\Collections\Collection $specifications
     */
    public function getSpecifications()
    {
        return $this->specifications;
    }
}