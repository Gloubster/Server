<?php

namespace Gloubster\Documents;



/**
 * Gloubster\Documents\Job
 */
class Job
{
    /**
     * @var MongoId $id
     */
    protected $id;

    /**
     * @var boolean $error
     */
    protected $error;

    /**
     * @var string $workerId
     */
    protected $workerId;

    /**
     * @var string $beginning
     */
    protected $beginning;

    /**
     * @var string $end
     */
    protected $end;

    /**
     * @var string $delivery
     */
    protected $delivery;

    /**
     * @var string $deliveryId
     */
    protected $deliveryId;

    /**
     * @var string $processDuration
     */
    protected $processDuration;

    /**
     * @var string $deliveryDuration
     */
    protected $deliveryDuration;

    /**
     * @var string $routingKey
     */
    protected $routingKey;

    /**
     * @var string $exchangeName
     */
    protected $exchangeName;


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
     * Set error
     *
     * @param boolean $error
     * @return Job
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
     * Set workerId
     *
     * @param string $workerId
     * @return Job
     */
    public function setWorkerId($workerId)
    {
        $this->workerId = $workerId;
        return $this;
    }

    /**
     * Get workerId
     *
     * @return string $workerId
     */
    public function getWorkerId()
    {
        return $this->workerId;
    }

    /**
     * Set beginning
     *
     * @param string $beginning
     * @return Job
     */
    public function setBeginning($beginning)
    {
        $this->beginning = $beginning;
        return $this;
    }

    /**
     * Get beginning
     *
     * @return string $beginning
     */
    public function getBeginning()
    {
        return $this->beginning;
    }

    /**
     * Set end
     *
     * @param string $end
     * @return Job
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get end
     *
     * @return string $end
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set delivery
     *
     * @param string $delivery
     * @return Job
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
        return $this;
    }

    /**
     * Get delivery
     *
     * @return string $delivery
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set deliveryId
     *
     * @param string $deliveryId
     * @return Job
     */
    public function setDeliveryId($deliveryId)
    {
        $this->deliveryId = $deliveryId;
        return $this;
    }

    /**
     * Get deliveryId
     *
     * @return string $deliveryId
     */
    public function getDeliveryId()
    {
        return $this->deliveryId;
    }

    /**
     * Set processDuration
     *
     * @param string $processDuration
     * @return Job
     */
    public function setProcessDuration($processDuration)
    {
        $this->processDuration = $processDuration;
        return $this;
    }

    /**
     * Get processDuration
     *
     * @return string $processDuration
     */
    public function getProcessDuration()
    {
        return $this->processDuration;
    }

    /**
     * Set deliveryDuration
     *
     * @param string $deliveryDuration
     * @return Job
     */
    public function setDeliveryDuration($deliveryDuration)
    {
        $this->deliveryDuration = $deliveryDuration;
        return $this;
    }

    /**
     * Get deliveryDuration
     *
     * @return string $deliveryDuration
     */
    public function getDeliveryDuration()
    {
        return $this->deliveryDuration;
    }

    /**
     * Set routingKey
     *
     * @param string $routingKey
     * @return Job
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    /**
     * Get routingKey
     *
     * @return string $routingKey
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * Set exchangeName
     *
     * @param string $exchangeName
     * @return Job
     */
    public function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
        return $this;
    }

    /**
     * Get exchangeName
     *
     * @return string $exchangeName
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }
    /**
     * @var string $errorMessage
     */
    protected $errorMessage;


    /**
     * Set errorMessage
     *
     * @param string $errorMessage
     * @return Job
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Get errorMessage
     *
     * @return string $errorMessage
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
