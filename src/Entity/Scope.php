<?php

namespace ZF\OAuth2\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Scope
 */
class Scope implements ArraySerializableInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var boolean
     */
    private $isDefault;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\OAuth2\Entity\Client
     */
    private $client;

    public function getArrayCopy()
    {
        return array(
            'id' => $this->getId(),
            'type' => $this->getType(),
            'scope' => $this->getScope(),
            'isDefault' => $this->getIsDefault(),
        );
    }

    public function exchangeArray(array $data) {
        throw new \Exception('exchange array not implemented');
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Scope
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return Scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return Scope
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set client
     *
     * @param \ZF\OAuth2\Entity\Client $client
     * @return Scope
     */
    public function setClient(\ZF\OAuth2\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \ZF\OAuth2\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
