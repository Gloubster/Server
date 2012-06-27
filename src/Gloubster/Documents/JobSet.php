<?php

namespace Gloubster\Documents;



/**
 * Gloubster\Documents\JobSet
 */
class JobSet
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $file
     */
    protected $file;


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
     * Set file
     *
     * @param string $file
     * @return JobSet
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get file
     *
     * @return string $file
     */
    public function getFile()
    {
        return $this->file;
    }
    /**
     * @var Gloubster\Documents\Specification
     */
    protected $specifications = array();

    public function __construct()
    {
        $this->specifications = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set specifications
     *
     * @param mixed $specifications
     */
    public function setSpecifications($specifications)
    {
        $this->specifications = $specifications;
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
