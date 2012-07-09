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
     * @var string $jobHandle
     */
    protected $jobHandle;

    /**
     * @var float $start
     */
    protected $start;

    /**
     * @var float $stop
     */
    protected $stop;

    /**
     * @var date $submittedOn
     */
    protected $submittedOn;

    /**
     * @var string $workerName
     */
    protected $workerName;

    /**
     * @var boolean $error
     */
    protected $error = false;

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

    /**
     * Set processing
     *
     * @param boolean $processing
     * @return Specification
     */
    public function setProcessing($processing)
    {
        $this->processing = $processing;
        return $this;
    }

    /**
     * Get processing
     *
     * @return boolean $processing
     */
    public function getProcessing()
    {
        return $this->processing;
    }

    /**
     * Set jobHandle
     *
     * @param string $jobHandle
     * @return Specification
     */
    public function setJobHandle($jobHandle)
    {
        $this->jobHandle = $jobHandle;
        return $this;
    }

    /**
     * Get jobHandle
     *
     * @return string $jobHandle
     */
    public function getJobHandle()
    {
        return $this->jobHandle;
    }

    /**
     * Set start
     *
     * @param float $start
     * @return Specification
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Get start
     *
     * @return float $start
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set stop
     *
     * @param float $stop
     * @return Specification
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
        return $this;
    }

    /**
     * Get stop
     *
     * @return float $stop
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * Set submittedOn
     *
     * @param date $submittedOn
     * @return Specification
     */
    public function setSubmittedOn($submittedOn)
    {
        $this->submittedOn = $submittedOn;
        return $this;
    }

    /**
     * Get submittedOn
     *
     * @return date $submittedOn
     */
    public function getSubmittedOn()
    {
        return $this->submittedOn;
    }

    /**
     * Set workerName
     *
     * @param string $workerName
     * @return Specification
     */
    public function setWorkerName($workerName)
    {
        $this->workerName = $workerName;
        return $this;
    }

    /**
     * Get workerName
     *
     * @return string $workerName
     */
    public function getWorkerName()
    {
        return $this->workerName;
    }

    /**
     * Set error
     *
     * @param boolean $error
     * @return Specification
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Get error
     *
     * @return boolean $error
     */
    public function getError()
    {
        return $this->error;
    }
    /**
     * @var Gloubster\Documents\JobSet
     */
    protected $jobset;


    /**
     * Set jobset
     *
     * @param Gloubster\Documents\JobSet $jobset
     * @return Specification
     */
    public function setJobset(\Gloubster\Documents\JobSet $jobset)
    {
        $this->jobset = $jobset;
        return $this;
    }

    /**
     * Get jobset
     *
     * @return Gloubster\Documents\JobSet $jobset
     */
    public function getJobset()
    {
        return $this->jobset;
    }
}
