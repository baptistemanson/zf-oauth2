<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2;

use Doctrine\ORM\Mapping\Driver\XmlDriver;

/**
 * ZF2 module
 */
class Module
{
    public function onBootstrap($e)
    {
        $app     = $e->getParam('application');
        $sm      = $app->getServiceManager();
        $config = $sm->get('Config');

        // Add the default entity driver only if specified in configuration
        if (isset($config['zf-oauth2']['storage_settings']['enable_default_entities'])
            && $config['zf-oauth2']['storage_settings']['enable_default_entities']) {
            $chain = $sm->get($config['zf-oauth2']['storage_settings']['driver']);
            $chain->addDriver(new XmlDriver(__DIR__ . '/config/xml'), 'ZF\OAuth2\Entity');
        }
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
