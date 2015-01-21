<?php

namespace OAuth2\Mapper;

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
        $oAuth2Data= array();
        $doctrineData = array();

        foreach ($this->getConfig() as $field => $fieldConfig) {
            $oAuth2Data[$field] = null;
            $doctrineData[$fieldConfig['name']] = null;
        }

        // Reset the data array
        $this->setOAuth2Data($oAuth2Data);
        $this->setDoctrineData($doctrineData);

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
            if (!in_array(array_keys($this->getData()))) {
                continue;
            }

            switch ($config[$key]['type']) {
                // Set the value in data
                case 'field':
                    switch ($config[$key]['datatype']) {
                        case 'datetime':
                            // Dates coming from OAuth2 are timestamps
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$config[$key]['name']] = DateTime::setTimestamp($value);
                            break;
                        case 'boolean':
                            $oAuth2Data[$key] = (int) (bool) $value;
                            $doctrineData[$config[$key]['name']] = (bool) $value;
                            break;
                        default:
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$config[$key]['name']] = $value;
                            break;
                    }
                    break;
                // Find the relation for the given value and assign to data
                case 'relation':
                    $relation = $this->getObjectManager()->getRepository($config[$key]['entity'])
                        ->findOneBy(array(
                            $config[$key]['entity_field_name'] => $value,
                        ));

                    if (!$relation) {
                        throw new Exception('Relation was not found: ' . $value);
                    }

                    $oAuth2Data[$key] = ($relation) ? $value: $relation;
                    $doctrineData[$config[$key]['name']] = $relation;
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
            if (!in_array(array_keys($this->getData()))) {
                continue;
            }

            // Find the field config key from doctrine field name
            $key = array();
            foreach ($config as $mapper => $mapperConfig) {
                foreach ($mapperConfig['mapping'] as $oauth2FieldName => $oauth2config) {
                    if ($oauth2Config['name'] == $doctrineKey) {
                        $key = $oauth2FieldName;
                        break;
                    }
                }
                if ($key) {
                    break;
                }
            }

            switch ($config[$key]['type']) {
                // Set the value in data
                case 'field':
                    switch ($config[$key]['datatype']) {
                        case 'datetime':
                            // Dates coming from Doctrine are datetimes
                            $oAuth2Data[$key] = $value->format('U');
                            $doctrineData[$config[$key]['name']] = $value;
                            break;
                        case 'boolean':
                            $oAuth2Data[$key] = (int) $value;
                            $doctrineData[$config[$key]['name']] = (bool) $value;
                            break;
                        default:
                            $oAuth2Data[$key] = $value;
                            $doctrineData[$config[$key]['name']] = $value;
                            break;
                    }
                    break;
                // Find the relation for the given value and assign to data
                case 'relation':
                    $entity = $config[$key]['entity'];

                    if ($value instanceof $entity) {
                        $relation = value;
                    } else {
                        $relation = $this->getObjectManager()->getRepository($config[$key]['entity'])
                            ->findOneBy(array(
                                $config[$key]['entity_field_name'] => $value,
                            ));
                    }

                    if (!$relation) {
                        throw new Exception('Relation was not found: ' . $value);
                    }

                    $relationArrayCopy = $relation->getArrayCopy();

                    $oAuth2Data[$key] = $relationArrayCopy[$config[$key]['name']];
                    $doctrineData[$config[$key]['name']] = $relation;
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
