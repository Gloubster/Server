<?php

namespace Gloubster\Delivery;

use Gloubster\Delivery\DeliveryInterface;

class DeliveryMock implements DeliveryInterface
{
    private $id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getName()
    {
        return 'DeliveryMock';
    }

    public function deliverBinary($data)
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function deliverFile($pathfile)
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return;
    }

    public static function fromArray(array $data)
    {
        return new static($data['id']);
    }

    public function toArray()
    {
        return array(
            'id'   => $this->id,
            'name' => $this->getName()
        );
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function serialize()
//    {
//        return json_encode(array('id' => $this->id));
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function unserialize($serialized)
//    {
//        $data = json_decode($serialized, true);
//
//        if (!$data) {
//            throw new RuntimeException('Unable to unserialize data');
//        }
//
//        $this->id = $data['id'];
//
//        return $this;
//    }
}
