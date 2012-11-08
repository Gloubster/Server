<?php

namespace Gloubster\Documents;



/**
 * Gloubster\Documents\Garbage
 */
class Garbage
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var string $data
     */
    protected $data;


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
     * Set data
     *
     * @param string $data
     * @return Garbage
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return string $data
     */
    public function getData()
    {
        return $this->data;
    }
}