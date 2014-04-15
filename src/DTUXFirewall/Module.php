<?php
namespace DTUXFirewall;

use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Config\Factory;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Mongo;
use Zend\Session\SaveHandler\MongoDB;
use Zend\Session\SaveHandler\MongoDBOptions;
use Zend\ModuleManager\Feature\InitProviderInterface;
use DTUXFirewall\Controller\Plugin\FirewallPlugin;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, InitProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);



        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions(array(
            'use_cookies'         => true,
            'cookie_httponly'     => true,
            'remember_me_seconds' => 1800,
            'name'                => 'DTuX'
        ));
        $sessionManager = new SessionManager($sessionConfig);
        $mongo          = new Mongo();
        $options        = new MongoDBOptions(array(
            'database'   => 'simpleDDNS',
            'collection' => 'PHPSession',
        ));
        $saveHandler    = new MongoDB($mongo, $options);
        $sessionManager->setSaveHandler($saveHandler);
        $sessionManager->start();

        Container::setDefaultManager($sessionManager);


    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return Factory::fromFile(__DIR__ . '/config/module.config.yml');
    }

    public function getAutoloaderConfig()
    {

    }

    public function getServiceConfig()
    {

        return array(

            'factories' => array(

                'DTUXFirewall\Auth\Adapter' => function ($sm) {
                        return new \DTUXFirewall\Auth\Adapter();
                },
                'Session' => function($sm) {
                        return new \Zend\Authentication\Storage\Session('DTuX');
                 },
                'DTUXFirewall\Service\Auth' => function($sm) {
                        $adapter = $sm->get('\DTUXFirewall\Auth\Adapter');
                        $entity = 'DTUXFirewall\Document\Usuario';
                        return new \DTUXFirewall\Service\Auth($adapter,$entity);
                 },

                'DTUXFirewall\Storage\FirewallStorage' => function ($sm) {
                        return new \DTUXFirewall\Storage\FirewallStorage();
                },
                'DTUXAcl' => function ($sm) {
                        return new \DTUXFirewall\Service\Acl($sm);
                }

            )
        );

    }

    /**
     * Initialize workflow
     *
     * @param  ModuleManagerInterface $manager
     * @return void
     */
    public function init(ModuleManagerInterface $manager)
    {
        $sharedEvents = $manager->getEventManager()->getSharedManager();
            $sharedEvents->attach("Zend\Mvc\Controller\AbstractActionController", 'dispatch', function($e) {
            $controllerPlugin = new FirewallPlugin();
            $controllerPlugin->preDispatch($e);
        }, 99);
    }
}
