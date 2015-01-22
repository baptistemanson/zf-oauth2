<?php

namespace ZF\OAuth2\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * AccessToken
 */
class AccessToken implements ArraySerializableInterface
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var string
     */
    private $scope;

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
        throw new \Exception('getArrayCopy not implemented');
    }

    public function exchangeArray(array $array)
    {
        foreach ($array as $key => $value) {
            $key = strtolower($key);
            switch ($key) {
                case 'accesstoken':
                    $this->setAccessToken($value);
                    break;
                case 'expires':
                    $this->setExpires($value);
                    break;
                case 'scope':
                    $this->setScope($value);
                    break;
                case 'client':
                    $this->setClient($value);
                    break;
                default:
                    break;
           }
       }

       return $this;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     * @return AccessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return AccessToken
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return AccessToken
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
     * @return AccessToken
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
