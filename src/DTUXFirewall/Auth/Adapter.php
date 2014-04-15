<?php

namespace DTUXFirewall\Auth;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;


/**
 * Adaptador padrão de autenticação
 * @category DTUXFirewall
 * @package DTUXFirewall
 * @subpackage Auth
 * @author Diego Pereira Grassato <diego.grassato@gmail.com>
 * @data 31/10/13 11:39
 */
class Adapter extends  \DTUXBase\Service\ServiceLocatorAware implements AdapterInterface
{
    protected $entity;
    protected $username;
    protected $password;


    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function loadAcl($credenciais){
        $acl = new Acl();


        /*$roleGuest = new Role('guest');
        $acl->addRole($roleGuest);
        $acl->addRole(new Role('diego'), $roleGuest);
        $acl->addRole(new Role('editor'), 'diego');
        $acl->addRole(new Role('administrator'));

        $acl->addResource(new Resource('slackware'));
        */

        /*$acl->addRole(new Role('editor'), 'diego');
        $acl->addRole(new Role('administrator'));

        $acl->addResource(new Resource('slackware'));
        $acl->allow($roleGuest, null, 'view');
        $acl->allow('diego', "slackware", array('edit','save', 'save'));
    */
        //var_dump($credenciais->getMeta());
        echo $credenciais->getId();
        print_r($credenciais->getAcl());
        $acls = $credenciais->getAcl();

        $roleGuest = new Role( $credenciais->getId() );
        $acl->addRole($roleGuest);
        foreach($acls as $usuarioPrivilegio){
                echo "Regra ".$usuarioPrivilegio['regra'] . " == > " .$usuarioPrivilegio['tipo'];

                //$acl->addRole($roleGuest);
                $acl->addRole(new Role( $usuarioPrivilegio['regra'] ), $roleGuest);
                $recurso = $usuarioPrivilegio['controller']['nome'];
                if(! $acl->hasResource($recurso) )
                    $acl->addResource(new Resource($recurso));


                echo "<ul> Controller => ".$usuarioPrivilegio['controller']['nome'] . " == > " .$usuarioPrivilegio['controller']['tipo'];

                $acl->allow($credenciais->getId() , $usuarioPrivilegio['controller']['nome'] , $usuarioPrivilegio['views']);

                /*foreach($usuarioPrivilegio['views'] as $usuarioRules){
                    echo "<br> view => ".$usuarioRules;

                }*/
                echo "</ul>";
        }
        echo "<br><br>";
        //echo $acl->isAllowed('534a4c640ace83f36d48496d', "Index", 'lista') ? "allowed" : "denied";

    }

    /**
     * @return Authentication
     */
    public function authenticate()
    {
        $repository = $this->getServiceLocator()->get('manager')->getRepository($this->getEntity());

        $credenciais = $repository->validaCredenciais($this->getUsername(),$this->getPassword());
        //$this->loadAcl($credenciais);
        if ( $credenciais instanceof \DTUXFirewall\Document\Usuario ) {
           // $hashSenha = $credenciais->encryptPassword($this->getPassword());
            //echo "<br>".$hashSenha . " - ".$credenciais->getPassword();
            //echo "<br>".$credenciais->getSalt();exit;
             return new Result(Result::SUCCESS, array('credenciais' => $credenciais), array('OK'));

        } else {
           return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, array());
        }
    }
}
