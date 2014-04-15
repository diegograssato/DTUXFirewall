<?php
namespace DTUXFirewall\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;

class FirewallPlugin extends AbstractPlugin
{


    public function preDispatch(MvcEvent $e)
    {
        /**
         * @var $controller \Zend\Mvc\Controller\AbstractController
         */
        $controller = $e->getTarget();
        $routeMatch = $e->getRouteMatch();;

        $acl =  $controller->getServiceLocator()->get('DTUXAcl');
        //var_dump( $acl->getResources() );
        //var_dump( $acl->getPrivilegios() );

         //$credenciais = $acl->getCredenciais();
        $controller = $routeMatch->getParam('controller','index');
        $action     = $routeMatch->getParam('action');
        $matchedRoute = $routeMatch->getMatchedRouteName();

        //$file = file_get_contents("data/spiffy-routes/zfcache-f2/zfcache-spiffy-routes.dat");


        /*$serializer =  \Zend\Serializer\Serializer::factory('phpserialize');
        $objSerializado =  $serializer->unserialize($file);
        //var_dump($objSerializado);
        foreach ($objSerializado as $key => $value) {
            echo "<br> => ". $key;
            foreach ($value as $key => $value) {
                echo "<br> ==>> ". $key ." = ".$value;
                foreach ($value as $key => $value) {
                    echo "<br>  ==>>> ". $key ." = ".$value;
                }
            }
        }*/
        //echo $acl->isAllowed($controller,"home") ? "allowed" : "denied";

        if(!$acl->hasIdentity()){

            if($matchedRoute <> "dtux-firewall-login"){

                    try{

                        $controller->flashMessenger()->setNamespace('danger')->addMessage('Você não está autenticado!');
                        return $controller->redirect()->toUrl("/auth/login");

                    }catch(\Exception $e){
                        echo "Plugin não encontrado";
                    }

            }
        }
       // $role = $controller->identity()->getRole()->getName();
        /**
         * @var $acl \WPAcl\Acl\PermissionControl
         */
       // $acl = $controller->getServiceLocator()->get('acl.permission.control');

        /**$routeMatch = $e->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action     = $routeMatch->getParam('action');
        */

        //echo "Controller " .$controller;

        /*if(!$acl->hasResource($controller)){
            throw new \Exception('Resource ' . $controller . ' not defined');
        }

        if (!$acl->isAllowed($role, $controller, $action)) {
            return $controller->redirect()->toRoute('home', array('controller' => 'home', 'action' => 'index'));
        }*/
    }




}