<?php

namespace ZF\OAuth2\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefreshToken
 */
class RefreshToken
{
    /**
     * @var string
     */
    private $refreshToken;

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
     * Set refreshToken
     *
     * @param string $refreshToken
     * @return RefreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken
     *
     * @return string 
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return RefreshToken
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
     * @return RefreshToken
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
     * @return RefreshToken
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
