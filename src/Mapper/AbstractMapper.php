<?php

namespace ZF\OAuth2\Mapper;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DateTime;
use Exception;

abstract class AbstractMapper implements ObjectManagerAwareInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ObjectManager
     */
    protected $config;

    /**
     * @var data
     */
    protected $oAuth2Data = array();

    /**
     * @var data
     */
    protected $doctrineData = array();

    public function reset()
    {
        $this->oauth2Data = array();
        $this->doctrineData = array();

        return $this;
    }

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

    protected function getOAuth2Data()
    {
        return $this->oAuth2Data;
    }

    protected function setOAuth2Data(array $data)
    {
        $this->oAuth2Data = $data;

        return $this;
    }

    protected function getDoctrineData()
    {
        return $this->doctrineData;
    }

    protected function setDoctrineData(array $data)
    {
        $this->doctrineData = $data;

        return $this;
    }

    /**
     * Set the mapping config
     *
     * @param array
     * @return this
     */
     public function setConfig(array $config)
     {
        $this->config = $config;

        return $this;
     }

    /**
     * Return the current config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Pass data formatted for the oauth2 server
     * and populate both oauth2 and doctrine data
     */
    public function exchangeOAuth2Array(array $array)
    {
        $oAuth2Data = $this->getOAuth2Data();
        $doctrineData = $this->getDoctrineData();
        $config = $this->getConfig();

        foreach ($array as $key => $value) {
            if (!isset($config['mapping'][$key])) {
                continue;
            }

            switch ($config['mapping'][$key]['type']) {
                // Set the value in data
                case 'field':
                    switch ($config['mapping'][$key]['datatype']) {
                        case 'datetime':
                            // Dates coming from OAuth2 are timestamps
                            $oAuth2Data[$key] = $value;
                            $date = new DateTime();
                            $date->setTimestamp($value);
                            $doctrineData[$config['mapping'][$key]['name']] = $date;
                            break;
                        case 'boolean':
                            $oAuth2Data[$key] = (int) (bool) $value;
                            $doctrineData[$config['mapping'][$key]['name']] = (bool) $value;
                            break;
                        default:
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$config['mapping'][$key]['name']] = $value;
                            break;
                    }
                    break;
                // Find the relation for the given value and assign to data
                case 'relation':
                    $relation = $this->getObjectManager()->getRepository($config['mapping'][$key]['entity'])
                        ->findOneBy(array(
                            $config['mapping'][$key]['entity_field_name'] => $value,
                        ));

                    if (!$relation) {
                        if (isset($config['mapping'][$key]['allowNull']) && $config['mapping'][$key]['allowNull']) {

                        } else {
                            throw new Exception("Relation was not found: $key: $value");
                        }
                    }

                    if ($relation) {
                        $oAuth2Data[$key] = $value;
                        $doctrineData[$config['mapping'][$key]['name']] = $relation;
                    } else {
                        $oAuth2Data[$key] = null;
                        $doctrineData[$config['mapping'][$key]['name']] = null;
                    }

                    break;
                default:
                    break;
            }

        }

        $this->setOAuth2Data($oAuth2Data);
        $this->setDoctrineData($doctrineData);

        return $this;
    }

    /**
     * Pass data formatted for the oauth2 server
     * and populate both oauth2 and doctrine data
     */
    public function exchangeDoctrineArray(array $array)
    {
        $oAuth2Data = $this->getOAuth2Data();
        $doctrineData = $this->getDoctrineData();
        $config = $this->getConfig();

        foreach ($array as $doctrineKey => $value) {
            // Find the field config key from doctrine field name
            $key = '';

            foreach ($config['mapping'] as $oAuth2FieldName => $oAuth2Config) {
                if ($oAuth2Config['name'] == $doctrineKey) {
                    $key = $oAuth2FieldName;
                    break;
                }
            }

            if (!$key) {
                continue;
            }

            switch ($config['mapping'][$key]['type']) {
                // Set the value in data
                case 'field':
                    switch ($config['mapping'][$key]['datatype']) {
                        case 'datetime':
                            // Dates coming from Doctrine are datetimes
                            $oAuth2Data[$key] = $value->format('U');
                            $doctrineData[$config['mapping'][$key]['name']] = $value;
                            break;
                        case 'boolean':
                            $oAuth2Data[$key] = (int) $value;
                            $doctrineData[$config['mapping'][$key]['name']] = (bool) $value;
                            break;
                        default:
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$config['mapping'][$key]['name']] = $value;
                            break;
                    }
                    break;
                // Find the relation for the given value and assign to data
                case 'relation':
                    $entity = $config['mapping'][$key]['entity'];

                    if ($value instanceof $entity) {
                        $relation = $value;
                    } else {
                        $relation = $this->getObjectManager()->getRepository($config['mapping'][$key]['entity'])
                            ->findOneBy(array(
                                $config['mapping'][$key]['entity_field_name'] => $value,
                            ));
                    }

                    if (!$relation) {
                        if (isset($config['mapping'][$key]['allowNull']) && $config['mapping'][$key]['allowNull']) {

                        } else {
                            throw new Exception("Relation was not found: $key: $value");
                        }
                    }

                    if ($relation) {
                        $oAuth2Data[$key] = $value;
                        $doctrineData[$config['mapping'][$key]['name']] = $relation;
                    } else {
                        $oAuth2Data[$key] = null;
                        $doctrineData[$config['mapping'][$key]['name']] = null;
                    }
                    break;
                default:
                    break;
            }
        }

        $this->setOAuth2Data($oAuth2Data);
        $this->setDoctrineData($doctrineData);

        return $this;
    }

    public function getOAuth2ArrayCopy()
    {
        return $this->getOAuth2Data();
    }

    public function getDoctrineArrayCopy()
    {
        return $this->getDoctrineData();
    }
}
