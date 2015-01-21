<?php

namespace OAuth2\Storage;

use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use OAuth2\Storage\AuthorizationCodeInterface,
use OAuth2\Storage\AccessTokenInterface,
use OAuth2\Storage\ClientCredentialsInterface,
use OAuth2\Storage\UserCredentialsInterface,
use OAuth2\Storage\RefreshTokenInterface,
use OAuth2\Storage\JwtBearerInterface,
use OAuth2\Storage\ScopeInterface,
use OAuth2\Storage\PublicKeyInterface,
use OAuth2\Storage\UserClaimsInterface,
use OAuth2\Storage\OpenIDAuthorizationCodeInterface
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use DateTime;

/**
 * Simple PDO storage for all storage types
 *
 * NOTE: This class is meant to get users started
 * quickly. If your application requires further
 * customization, extend this class or create your own.
 *
 * NOTE: Passwords are stored in plaintext, which is never
 * a good idea.  Be sure to override this for your application
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class DoctrineAdapter implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    JwtBearerInterface,
    ScopeInterface,
    PublicKeyInterface,
    UserClaimsInterface,
    OpenIDAuthorizationCodeInterface,
    ObjectManagerAwareInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set the object manager
     *
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get the object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @var array
     */
    protected $config = array(
        'client_entity'             => 'OAuth2\Doctrine\Entity\Client',
        'access_token_entity'       => 'OAuth2\Doctrine\Entity\AccessToken',
        'refresh_token_entity'      => 'OAuth2\Doctrine\Entity\RefreshToken',
        'authorization_code_entity' => 'OAuth2\Doctrine\Entity\Code',
        'user_entity'               => 'OAuth2\Doctrine\Entity\User',
        'jwt_entity'                => 'OAuth2\Doctrine\Entity\Jwt',
        'jti_entity'                => 'OAuth2\Doctrine\Entity\Jti',
        'scope_entity'              => 'OAuth2\Doctrine\Entity\Scope',
        'public_key_entity'         => 'OAuth2\Doctrine\Entity\PublicKey',
    );

    /**
     * Set the config for the entities implementing the interfaces
     *
     * @param config array
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config), $config);
    }

    // plaintext passwords are bad!  Override this for your application
    protected function checkPassword($user, $password)
    {
        return $user->getPassword() == sha1($password);
    }

    /* OAuth2\Storage\ClientCredentialsInterface */
    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials($client_id, $client_secret)
    {
        return (bool) $this->getObjectManager()
            ->getRepository($this->config['client_entity'])
            ->findOneBy(
                array(
                    'id'     => $client_id,
                    'secret' => $client_secret,
                )
            );
    }

    /* OAuth2\Storage\ClientCredentialsInterface */
    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return
     * TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient($client_id)
    {
        $client = (bool) $this->getObjectManager()
            ->getRepository($this->config['client_entity'])
            ->find($client_id);

        if (!$client) {
            throw new Exception('Client not found');
        }

        return empty($client->getSecret());
    }


    /* OAuth2\Storage\ClientInterface */
    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return array
     *               Client details. The only mandatory key in the array is "redirect_uri".
     *               This function MUST return FALSE if the given client does not exist or is
     *               invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     *               <code>
     *               return array(
     *               "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *               "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *               "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *               "user_id"      => USER_ID,           // OPTIONAL the user identifier associated with this client
     *               "scope"        => SCOPE,             // OPTIONAL the scopes allowed for this client
     *               );
     *               </code>
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails($client_id)
    {
        return $this->getObjectManager()
            ->getRepository($this->config['client_entity'])
            ->find($client_id)->toArray();
    }

    /* !!!!! OAuth2\Storage\ClientInterface */
    /**
     * This function isn't in the interface but called often
     */
    public function setClientDetails($client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope = null,
        $user_id = null)
    {
        $client = $this->getObjectManager()
            ->getRepository($this->config['client_entity'])
            ->find($client_id)->toArray();

        if (!$client) {
            $client = new $this->config['client_entity'];
            $client->setClientId($client_id);
            $this->getObjectManager()->persist($client);
        }

        $client->exchangeArray(array(
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_types' => $grant_types,
            'scope' => $scope,
            'user_id' => $user_id,
        ));

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\ClientInterface */
    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $grant_type
     * Grant type to be check with
     *
     * @return
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $client = (bool) $this->getObjectManager()
            ->getRepository($this->config['client_entity'])
            ->find($client_id);

        if (!$client) {
            throw new Exception('Client not found');
        }

        if ($client->getGrantType()) {
            $grantTypes = (array) explode(' ', $client->getGrantType());

            return in_array($grant_type, $grantTypes);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /* OAuth2\Storage\ClientInterface */
    /**
     * Get the scope associated with this client
     *
     * @return
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        $client = (bool) $this->getObjectManager()
            ->getRepository($this->config['client_entity'])
            ->find($client_id);

        if (!$client) {
            return false;
        }

        return $client->getScope();
    }

    /* OAuth2\Storage\AccessTokenInterface */
    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param $oauth_token
     * oauth_token to be check with.
     *
     * @return
     * An associative array as below, and return NULL if the supplied oauth_token
     * is invalid:
     * - expires: Stored expiration in unix timestamp.
     * - client_id: (optional) Stored client identifier.
     * - user_id: (optional) Stored user identifier.
     * - scope: (optional) Stored scope values in space-separated string.
     * - id_token: (optional) Stored id_token (if "use_openid_connect" is true).
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken($access_token)
    {
        $accessToken = $this->getObjectManager()
            ->getRepository($this->config['access_token_entity'])
            ->find($access_token);

        return ($accessToken) ? $accessToken->toArray(): null;
    }

    /* OAuth2\Storage\AccessTokenInterface */
    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param $oauth_token    oauth_token to be stored.
     * @param $client_id      client identifier to be stored.
     * @param $user_id        user identifier to be stored.
     * @param int    $expires expiration to be stored as a Unix timestamp.
     * @param string $scope   OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken($access_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null)
    {
        $accessToken = $this->getObjectManager()
            ->getRepository($this->config['access_token_entity'])
            ->find($access_token);

        if (!$accessToken) {
            $accessToken = new $this->config['access_token_entity'];
            $accessToken->setAccessToken($access_token);
            $this->getObjectManager->persist($accessToken);
        }

        $expires = DateTime::setTimestamp($expires);

        $accessToken->exchangeArray(array(
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope,
        ));

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * Fetch authorization code data (probably the most common grant type).
     *
     * Retrieve the stored data for the given authorization code.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be check with.
     *
     * @return
     * An associative array as below, and NULL if the code is invalid
     * @code
     * return array(
     *     "client_id"    => CLIENT_ID,      // REQUIRED Stored client identifier
     *     "user_id"      => USER_ID,        // REQUIRED Stored user identifier
     *     "expires"      => EXPIRES,        // REQUIRED Stored expiration in unix timestamp
     *     "redirect_uri" => REDIRECT_URI,   // REQUIRED Stored redirect URI
     *     "scope"        => SCOPE,          // OPTIONAL Stored scope values in space-separated string
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1
     *
     * @ingroup oauth2_section_4
     */
    public function getAuthorizationCode($code)
        $authorizationCode = $this->getObjectManager()
            ->getRepository($this->config['authorization_code_entity'])
            ->find($code);

        return ($authorizationCode) ? $authorizationCode->toArray(): null;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * Take the provided authorization code values and store them somewhere.
     *
     * This function should be the storage counterpart to getAuthCode().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param string $code         Authorization code to be stored.
     * @param mixed  $client_id    Client identifier to be stored.
     * @param mixed  $user_id      User identifier to be stored.
     * @param string $redirect_uri Redirect URI(s) to be stored in a space-separated string.
     * @param int    $expires      Expiration to be stored as a Unix timestamp.
     * @param string $scope        OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
     # id_token added from pdo storage
    public function setAuthorizationCode($code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null)
    {
        $authorizationCode = $this->getObjectManager()
            ->getRepository($this->config['authorization_code_entity'])
            ->find($code);

        if (!$authorizationCode) {
            $authorizationCode = new $this->config['authorization_code_entity'];
            $authorizationCode->setAuthorizationCode($code);
            $this->getObjectManager()->persist($authorizationCode);
        }

        $authorizationCode->exchangeArray(array(
            'client_id' => $client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
            'id_token' => $id_token,
        ));

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * once an Authorization Code is used, it must be exipired
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
     *
     *    The client MUST NOT use the authorization code
     *    more than once.  If an authorization code is used more than
     *    once, the authorization server MUST deny the request and SHOULD
     *    revoke (when possible) all tokens previously issued based on
     *    that authorization code
     *
     */
    public function expireAuthorizationCode($code)
    {
        $authorizationCode = $this->getObjectManager()
            ->getRepository($this->config['authorization_code_entity'])
            ->find($code);

        if ($authorizationCode) {
            $this->getObjectManager()->remove($authorizationCode);
            $this->getObjectManager()->flush();
        }

        return true;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    /**
     * Grant access tokens for basic user credentials.
     *
     * Check the supplied username and password for validity.
     *
     * You can also use the $client_id param to do any checks required based
     * on a client, if you need that.
     *
     * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
     *
     * @param $username
     * Username to be check with.
     * @param $password
     * Password to be check with.
     *
     * @return
     * TRUE if the username and password are valid, and FALSE if it isn't.
     * Moreover, if the username and password are valid, and you want to
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.3
     *
     * @ingroup oauth2_section_4
     */
    public function checkUserCredentials($username, $password)
    {
        $user = $this->getObjectManager()
            ->getRepository($this->config['user_entity'])
            ->findOneBy(array('username' => $username));

        if ($user) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    /**
     * @return
     * ARRAY the associated "user_id" and optional "scope" values
     * This function MUST return FALSE if the requested user does not exist or is
     * invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     * return array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($username)
    {
        $user = $this->getObjectManager()
            ->getRepository($this->config['user_entity'])
            ->findOneBy(array('username' => $username));

        return ($user) ? $user->getArrayCopy(): null;
    }




    /* UserClaimsInterface */
/*
    public function getUserClaims($user_id, $claims)
    {
        if (!$userDetails = $this->getUserDetails($user_id)) {
            return false;
        }

        $claims = explode(' ', trim($claims));
        $userClaims = array();

        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    // address is an object with subfields
                    $userClaims['address'] = $this->getUserClaim($validClaim, $userDetails['address'] ?: $userDetails);
                } else {
                    $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
                }
            }
        }

        return $userClaims;
    }

    protected function getUserClaim($claim, $userDetails)
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);

        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        }

        return $userClaims;
    }
*/


    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Refresh token identifier.
     * - client_id: Client identifier.
     * - user_id: User identifier.
     * - expires: Expiration unix timestamp, or 0 if the token doesn't expire.
     * - scope: (optional) Scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    # If expired return null
    public function getRefreshToken($refresh_token)
    {
        $refreshToken = $this->getObjectManager()
            ->getRepository($this->config['refresh_token_entity'])
            ->findOneBy(array('refresh_token' => $refresh_token));

        $now = new DateTime();
        if (!$refreshToken || $refreshToken->getExpire() >= $now) {
            return null;
        }

        return $refreshToken->getArrayCopy();
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * Expiration timestamp to be stored. 0 if the token doesn't expire.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_6
     */
    public function setRefreshToken($refresh_token,
        $client_id,
        $user_id,
        $expires,
        $scope = null)
    {
        $refreshToken = $this->getObjectManager()
            ->getRepository($this->config['refresh_token_entity'])
            ->findOneBy(array('refresh_token' => $refresh_token));

        if (!$refreshToken) {
            $refreshToken = new $this->config['refresh_token_entity'];
            $refreshToken->setRefreshToken($refresh_token);
            $this->getObjectManager()->persist($refreshToken);
        }

        $expires = DateTime::setTimestamp($expires);

        $refreshToken->exchangeArray(array(
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope,
        ));

        $this->getObjectManager()->flush();

        return true;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expirse.
     *
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken($refresh_token)
    {
        $refreshToken = $this->getObjectManager()
            ->getRepository($this->config['refresh_token_entity'])
            ->findOneBy(array('refresh_token' => $refresh_token));

        if ($refreshToken) {
            $refreshToken->setExpire(new DateTime());
            $this->getObjectManager()->flush();
        }

        return true;
    }

    /* OAuth2\Storage\ScopeInterface */
    /**
     * Check if the provided scope exists.
     *
     * @param $scope
     * A space-separated string of scopes.
     *
     * @return
     * TRUE if it exists, FALSE otherwise.
     */
    public function scopeExists($scope)
    {
        $whereIn = implode(',', array_fill(0, count($scope), '?'));

        $queryBuilder = $this->getObjectManager()->createQueryBuilder()
            ->select('scope')
            ->from($this->config['scope_entity'], 'scope')
            ->andwhere(
                $queryBuilder->expr()->in('scope.scope', $scopeArray)
            )
            ;

        $result = $queryBuilder->getQuery()->getResult();

        return sizeof($result) == sizeof($whereIn);
    }

    /* OAuth2\Storage\ScopeInterface */
    /**
     * The default scope to use in the event the client
     * does not request one. By returning "false", a
     * request_error is returned by the server to force a
     * scope request by the client. By returning "null",
     * opt out of requiring scopes
     *
     * @param $client_id
     * An optional client id that can be used to return customized default scopes.
     *
     * @return
     * string representation of default scope, null if
     * scopes are not defined, or false to force scope
     * request by the client
     *
     * ex:
     *     'default'
     * ex:
     *     null
     */
     # client_id is not used?
    public function getDefaultScope($client_id = null);
    {
        $scope = $this->getObjectManager()
            ->getRepository($this->config['scope_entity'])
            ->findBy(array(
                'is_default' => true,
            ));

        $return = array();
        foreach ($scope as $s) {
            $return[] = $s->getScope();
        }

        return implode(' ', $return);
    }

    /* OAuth2\Storage\JWTBearerInterface */
    /**
     * Get the public key associated with a client_id
     *
     * @param $client_id
     * Client identifier to be checked with.
     *
     * @return
     * STRING Return the public key for the client_id if it exists, and MUST return FALSE if it doesn't.
     */
    public function getClientKey($client_id, $subject)
    {
        $scope = $this->getObjectManager()
            ->getRepository($this->config['jwt_entity'])
            ->findOneBy(array(
                'client_id' => $client_id,
                'subject' => $subject,
            ));

        return ($scope) ? $scope->getPublicKey(): false;
    }

    /* OAuth2\Storage\JwtBearerInterface */
    /**
     * Get a jti (JSON token identifier) by matching against the client_id, subject, audience and expiration.
     *
     * @param $client_id
     * Client identifier to match.
     *
     * @param $subject
     * The subject to match.
     *
     * @param $audience
     * The audience to match.
     *
     * @param $expiration
     * The expiration of the jti.
     *
     * @param $jti
     * The jti to match.
     *
     * @return
     * An associative array as below, and return NULL if the jti does not exist.
     * - issuer: Stored client identifier.
     * - subject: Stored subject.
     * - audience: Stored audience.
     * - expires: Stored expiration in unix timestamp.
     * - jti: The stored jti.
     */
    public function getJti($client_id,
        $subject,
        $audience,
        $expiration,
        $jti)
    {
        $expiration = DateTime::setTimestamp($expiration);

        $jtiEntity = $this->getObjectManager()
            ->getRepository($this->config['jwt_entity'])
            ->findOneBy(array(
                'issuer' => $client_id,
                'subject' => $subject,
                'audience' => $audience,
                'expires' => $expiration,
                'jti' => $jti
            ));

        return ($jtiEntity) ? $jtiEntity->getArrayCopy(): null;
    }

    /* OAuth2\Storage\JwtBearerInterface */
    /**
     * Store a used jti so that we can check against it to prevent replay attacks.
     * @param $client_id
     * Client identifier to insert.
     *
     * @param $subject
     * The subject to insert.
     *
     * @param $audience
     * The audience to insert.
     *
     * @param $expiration
     * The expiration of the jti.
     *
     * @param $jti
     * The jti to insert.
     */
    public function setJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $jtiEntity = new $this->config['jti_entity'];

        $expiration = DateTime::setTimestamp($expiration);

        $jtiEntity->exchangeArray(array(
            'client_id'  => $client_id,
            'subject'    => $subject,
            'audience'   => $audience,
            'expiration' => $expiration,
            'jti'        => $jti,
        ));

        $this->getObjectManager()->persist($jtiEntity);
        $this->getObjectManager()->flush();

        return true;
    }










    /* PublicKeyInterface */
    public function getPublicKey($client_id = null)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT public_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_BOTH)) {
            return $result['public_key'];
        }
    }

    public function getPrivateKey($client_id = null)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT private_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_BOTH)) {
            return $result['private_key'];
        }
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT encryption_algorithm FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_BOTH)) {
            return $result['encryption_algorithm'];
        }

        return 'RS256';
    }

    /**
     * DDL to create OAuth2 database and tables for PDO storage
     *
     * @see https://github.com/dsquier/oauth2-server-php-mysql
     */
    public function getBuildSql($dbName = 'oauth2_server_php')
    {
        $sql = "
        CREATE TABLE {$this->config['client_table']} (
          client_id             VARCHAR(80)   NOT NULL,
          client_secret         VARCHAR(80)   NOT NULL,
          redirect_uri          VARCHAR(2000),
          grant_types           VARCHAR(80),
          scope                 VARCHAR(4000),
          user_id               VARCHAR(80),
          PRIMARY KEY (client_id)
        );

        CREATE TABLE {$this->config['access_token_table']} (
          access_token         VARCHAR(40)    NOT NULL,
          client_id            VARCHAR(80)    NOT NULL,
          user_id              VARCHAR(80),
          expires              TIMESTAMP      NOT NULL,
          scope                VARCHAR(4000),
          PRIMARY KEY (access_token)
        );

        CREATE TABLE {$this->config['code_table']} (
          authorization_code  VARCHAR(40)    NOT NULL,
          client_id           VARCHAR(80)    NOT NULL,
          user_id             VARCHAR(80),
          redirect_uri        VARCHAR(2000),
          expires             TIMESTAMP      NOT NULL,
          scope               VARCHAR(4000),
          id_token            VARCHAR(1000),
          PRIMARY KEY (authorization_code)
        );

        CREATE TABLE {$this->config['refresh_token_table']} (
          refresh_token       VARCHAR(40)    NOT NULL,
          client_id           VARCHAR(80)    NOT NULL,
          user_id             VARCHAR(80),
          expires             TIMESTAMP      NOT NULL,
          scope               VARCHAR(4000),
          PRIMARY KEY (refresh_token)
        );

        CREATE TABLE {$this->config['user_table']} (
          username            VARCHAR(80),
          password            VARCHAR(80),
          first_name          VARCHAR(80),
          last_name           VARCHAR(80),
          email               VARCHAR(80),
          email_verified      BOOLEAN,
          scope               VARCHAR(4000)
        );

        CREATE TABLE {$this->config['scope_table']} (
          scope               VARCHAR(80)  NOT NULL,
          is_default          BOOLEAN,
          PRIMARY KEY (scope)
        );

        CREATE TABLE {$this->config['jwt_table']} (
          client_id           VARCHAR(80)   NOT NULL,
          subject             VARCHAR(80),
          public_key          VARCHAR(2000) NOT NULL
        );

        CREATE TABLE {$this->config['jti_table']} (
          issuer              VARCHAR(80)   NOT NULL,
          subject             VARCHAR(80),
          audiance            VARCHAR(80),
          expires             TIMESTAMP     NOT NULL,
          jti                 VARCHAR(2000) NOT NULL
        );

        CREATE TABLE {$this->config['public_key_table']} (
          client_id            VARCHAR(80),
          public_key           VARCHAR(2000),
          private_key          VARCHAR(2000),
          encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
        )
";

        return $sql;
    }

// dup with client interface
    public function getClientScope($client_id)
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }
}
