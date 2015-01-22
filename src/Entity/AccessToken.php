<?php

namespace ZF\OAuth2\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessToken
 */
class AccessToken
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
