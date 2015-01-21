<?php

return array(
    'zf-oauth2' => array
        'storage' => 'ZF\OAuth2\Adapter\DoctrineAdapter',
        'storage_settings' => array(
            # datatype is provided as a convienicne to help create the ERD
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'mapping' => array(
                'ZF\OAuth2\Mapper\User' => array(
                    'entity' => 'ZF\OAuth2\Entity\User',
                    'mapping' => array(
                        'username' => array(
                            'type' => 'field',
                            'name' => 'username',
                            'datatype' => 'string',
                        ),
                        'password' => array(
                            'type' => 'field',
                            'name' => 'password',
                            'datatype' => 'string',
                        ),
                        'first_name' => array(
                            'type' => 'field',
                            'name' => 'firstName',
                            'datatype' => 'string',
                        ),
                        'last_name' => array(
                            'type' => 'field',
                            'name' => 'lastName',
                            'datatype' => 'string',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\Client' => array(
                    'entity' => 'ZF\OAuth2\Entity\Client',
                    'mapping' => array(
                        'secret' => array(
                            'type' => 'field',
                            'name' => 'secret',
                            'datatype' => 'string',
                        ),
                        'redirect_uri' => array(
                            'type' => 'field',
                            'name' => 'redirectUri',
                            'datatype' => 'text',
                        ),
                        'grant_type' => array(
                            'type' => 'field',
                            'name' => 'grantType',
                            'datatype' => 'string',
                        ),
                        'scope' => array(
                            'type' => 'field',
                            'name' => 'scope',
                            'datatype' => 'text',
                        ),
                        'user_id' => array(
                            'type' => 'relation',
                            'name' => 'user',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\User',
                            'datatype' => 'integer',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\AccessToken' => array(
                    'entity' => 'ZF\OAuth2\Entity\AccessToken',
                    'mapping' => array(
                        'access_token' => array(
                            'type' => 'field',
                            'name' => 'accessToken',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                        ),
                        'scope' => array(
                            'type' => 'field',
                            'name' => 'scope',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\RefreshToken' => array(
                    'entity' => 'ZF\OAuth2\Entity\RefreshToken',
                    'mapping' => array(
                        'refresh_token' => array(
                            'type' => 'field',
                            'name' => 'refreshToken',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                        ),
                        'scope' => array(
                            'type' => 'field',
                            'name' => 'scope',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\AuthorizationCode' => array(
                    'entity' => 'ZF\OAuth2\Entity\Code',
                    'mapping' => array(
                        'authorization_code' => array(
                            'type' => 'field',
                            'name' => 'authorizationCode',
                        ),
                        'redirect_uri' => array(
                            'type' => 'field',
                            'name' => 'redirectUri',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                        ),
                        'scope' => array(
                            'type' => 'field',
                            'name' => 'scope',
                        ),
                        'id_token' => array(
                            'type' => 'field',
                            'name' => 'idToken',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\Jwt' => array(
                    'entity' => 'ZF\OAuth2\Entity\Jwt',
                    'mapping' => array(
                        'subject' => array(
                            'type' => 'field',
                            'name' => 'subject',
                        ),
                        'public_key' => array(
                            'type' => 'field',
                            'name' => 'publicKey',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\Jti' => array(
                    'entity' => 'ZF\OAuth2\Entity\Jti',
                    'mapping' => array(
                        'subject' => array(
                            'type' => 'field',
                            'name' => 'subject',
                        ),
                        'audience' => array(
                            'type' => 'field',
                            'name' => 'audience',
                        ),
                        'expires' => array(
                            'type' => 'field',
                            'name' => 'expires',
                        ),
                        'jti' => array(
                            'type' => 'field',
                            'name' => 'jti',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\Scope' => array(
                    'entity' => 'ZF\OAuth2\Entity\Scope',
                    'mapping' => array(
                        'type' => array(
                            'type' => 'field',
                            'name' => 'type',
                        ),
                        'scope' => array(
                            'type' => 'field',
                            'name' => 'scope',
                        ),
                        'is_default' => array(
                            'type' => 'field',
                            'name' => 'isDefault',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),

                'ZF\OAuth2\Mapper\PublicKey' => array(
                    'entity' => 'ZF\OAuth2\Entity\PublicKey',
                    'mapping' => array(
                        'public_key' => array(
                            'type' => 'field',
                            'name' => 'publicKey',
                        ),
                        'private_key' => array(
                            'type' => 'field',
                            'name' => 'privateKey',
                        ),
                        'encryption_algorithm' => array(
                            'type' => 'field',
                            'name' => 'encryptionAlgorithm',
                        ),
                        'client_id' => array(
                            'type' => 'relation',
                            'name' => 'client',
                            'entity_field_name' => 'id',
                            'entity' => 'OAuth2\Entity\Client',
                        ),
                    ),
                ),
            ),

        ),
    ),
),