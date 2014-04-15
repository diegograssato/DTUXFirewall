<?php

/**
 * Acl Service - essentially a helper to populate the underlying Zend Acl instance
 */

namespace DTUXFirewall\Service;

use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

use DTUXFirewall\Document\Privilegio;

class Acl
{

    protected $acl;

    protected $serviceLocator;

    protected $privilegios = array();

    /**
     * @var AuthenticationService
     */
    protected $_authService = null;

    const SHORT_ENTITY = 'credenciais';

    public function __construct($sm)
    {
        $this->serviceLocator = $sm;
        $this->acl = new ZendAcl;
        $this->setRoleConfig();
    }


    public function setServiceLocator(ServiceLocatorInterface $services)
    {

        $this->serviceLocator = $services;

        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Return role for the current authenticated identity
     * @return string
     */
    public function getCurrentRoleId()
    {

        if( $user = $this->getCredenciais() ) {

            $roleId = $user->getId();

            if( !empty( $roleId ) && $this->acl->hasRole( $roleId ) ) {
                return $roleId;
            }
        }

        return false;
    }



    public function getRoles()
    {
        return $this->acl->getRoles();
    }

    public function getResources()
    {
        return $this->acl->getResources();
    }

    public function getPrivilegios()
    {
        return $this->privilegios;
    }


    /**
     * This is named so that setRoles() addRoles() can be added later
     * For now this is a simple way to get role config into the ACL
     * @param array $roles
     * @return self
     */
    public function setRoleConfig()
    {
        $role = new Role( $this->getCurrentRoleId() );

        $this->acl->addRole( $role );

        if( null !== $this->getCredenciais() ) {

            $acls = $this->getCredenciais()->getAcl();

            foreach ( $acls as $usuarioPrivilegio ) {

                // echo ""<br> Regra ".$usuarioPrivilegio['regra'] . " == > " .$usuarioPrivilegio['tipo'];
                $this->acl->addRole( new Role( $usuarioPrivilegio['regra'] ), $role );
                $recurso = $usuarioPrivilegio['controller']['nome'];

                if ( !$this->acl->hasResource( $recurso ) )
                     $this->acl->addResource( new Resource( $recurso ) );

                //echo "<br> Controller => ".$usuarioPrivilegio['controller']['nome' ] . " == > " .$usuarioPrivilegio['controller']['tipo'];
                $view = null;
                if ( $usuarioPrivilegio['controller']['tipo'] == 'controller' )
                    $view = $usuarioPrivilegio['views'];

                //var_dump($view);
                if ( $usuarioPrivilegio['tipo'] == Privilegio::PRIVILEGIO_LIBERADO )
                    $this->acl->allow( $this->getCurrentRoleId() , $usuarioPrivilegio['controller']['nome'] , $view );

                if ( $usuarioPrivilegio['tipo'] == Privilegio::PRIVILEGIO_BLOQUEADO )
                    $this->acl->deny( $this->getCurrentRoleId() , $usuarioPrivilegio['controller']['nome'] ,$view );

                $this->privilegios[] = $usuarioPrivilegio['views'];

            }
        }

    }

    public function isAllowed($resource, $priv = NULL)
    {

        $role = $this->getCurrentRoleId();

        //var_dump($role);
        if ( !$this->acl->hasResource( $resource ) )
            return false;

        return $this->acl->isAllowed( $role, $resource, $priv);

        return false;
    }

    /**
     * Check if Identity is present
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return $this->getAuthService()->hasIdentity();
    }

    /**
     * Return current Identity
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        return $this->getAuthService()->getIdentity();
    }

    /**
     * Return current Identity
     *
     * @return mixed|null
     */
    public function getCredenciais()
    {
        return $this->getAuthService()->getIdentity()[self::SHORT_ENTITY];
    }

    /**
     * Sets Auth Service
     *
     * @param \Zend\Authentication\AuthenticationService $authService
     * @return UserAuthentication
     */
    public function setAuthService( AuthenticationService $authService )
    {
        $this->_authService = $authService;
        return $this;
    }

    /**
     * Gets Auth Service
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if ( null === $this->_authService ) {

            $session = $this->getServiceLocator()->get('Session');

            $this->setAuthService( new AuthenticationService() );
            $this->getAuthService()->setStorage( $session );
        }

        return $this->_authService;
    }


}