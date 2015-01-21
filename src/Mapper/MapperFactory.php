<?php

namespace ZF\Apigility\Doctrine\Server\Resource;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractDoctrineResourceFactory
 *
 * @package ZF\Apigility\Doctrine\Server\Resource
 */
class MapperFactory implements AbstractFactoryInterface
{

    /**
     * Cache of canCreateServiceWithName lookups
     * @var array
     */
    protected $lookupCache = array();

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return bool
     * @throws \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (array_key_exists($requestedName, $this->lookupCache)) {
            return $this->lookupCache[$requestedName];
        }

        if (!$serviceLocator->has('Config')) {
            // @codeCoverageIgnoreStart
            return false;
        }
            // @codeCoverageIgnoreEnd

        // Validate object is set
        $config = $serviceLocator->get('Config');

        if (!isset($config['zf-oauth2']['mapper'])
            || !isset($config['zf-oauth2']['mapper'][$requestedName])
        ) {
            $this->lookupCache[$requestedName] = false;

            return false;
        }

        // Validate if class a valid Mapper
        $className = $requestedName;
        $className = $this->normalizeClassname($className);
        $reflection = new \ReflectionClass($className);
        if (!$reflection->isSubclassOf('\ZF\OAuth2\Mapper\AbstractMapper')) {
            // @codeCoverageIgnoreStart
            throw new ServiceNotFoundException(
                sprintf(
                    '%s requires that a valid class is specified for factory %s; no service found',
                    __METHOD__,
                    $requestedName
                )
            );
        }
        // @codeCoverageIgnoreEnd

        // Validate object manager
        $config = $config['zf-oauth2']['object_manager'];
        if (!isset($config)) {
            // @codeCoverageIgnoreStart
            throw new ServiceNotFoundException(
                sprintf(
                    '%s requires that a valid "object_manager" is specified for listener %s; no service found',
                    __METHOD__,
                    $requestedName
                )
            );
        }
            // @codeCoverageIgnoreEnd

        $this->lookupCache[$requestedName] = true;

        return true;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     *
     * @return DoctrineResource
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $serviceLocator->get('Config');
        $mappingConfig = $config['zf-oauth2']['mapping'][$requestedName];

        $className = $requestedName;
        $className = $this->normalizeClassname($className);

        $objectManager = $this->loadObjectManager($serviceLocator, $config['zf-oauth2']);

        $mapper = new $className();
        $mapper->setObjectManager($objectManager);
        $mapper->setConfig($mappingConfig);

        return $mapper;
    }

    /**
     * @param $className
     *
     * @return string
     */
    protected function normalizeClassname($className)
    {
        return '\\' . ltrim($className, '\\');
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $config
     *
     * @return ObjectManager
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function loadObjectManager(ServiceLocatorInterface $serviceLocator, $config)
    {
        if ($serviceLocator->has($config['object_manager'])) {
            $objectManager = $serviceLocator->get($config['object_manager']);
        } else {
            // @codeCoverageIgnoreStart
            throw new ServiceNotCreatedException('The object_manager could not be found.');
        }
        // @codeCoverageIgnoreEnd
        return $objectManager;
    }
}
