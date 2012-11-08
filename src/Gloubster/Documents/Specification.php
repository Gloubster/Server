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
     * @var string $jobHandle
     */
    protected $jobHandle;

    /**
     * @var date $submittedOn
     */
    protected $submittedOn;

    /**
     * @var string $workerName
     */
    protected $workerName;

    /**
     * @var float $start
     */
    protected $start;

    /**
     * @var float $stop
     */
    protected $stop;

    /**
     * @var boolean $done
     */
    protected $done = false;

    /**
     * @var boolean $error
     */
    protected $error = false;

    /**
     * @var Gloubster\Documents\JobSet
     */
    protected $jobset;

    /**
     * @var Gloubster\Documents\Parameter
     */
    protected $parameters = array();

    public function __construct()
    {
        $this->parameters = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @var string $timers
     */
    protected $timers;


    /**
     * Set timers
     *
     * @param string $timers
     * @return Specification
     */
    public function setTimers($timers)
    {
        $this->timers = $timers;
        return $this;
    }

    /**
     * Get timers
     *
     * @return string $timers
     */
    public function getTimers()
    {
        return $this->timers;
    }
}
