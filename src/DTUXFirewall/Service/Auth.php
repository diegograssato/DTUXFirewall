<?php
namespace DTUXFirewall\Service;


use Zend\Authentication\AuthenticationService;
/**
 * Serviço responsável pela autenticação da aplicação
  *
 * @category DTUXFirewall
 * @package Service
 * @author  Diego Pereira Grassato <diego.grassato@gmail.com>
 */
class Auth extends ServiceLocatorAware
{
    /**
     * Adapter usado para a autenticação
     * @var DTUXFirewall\Auth\Adapter
     */
    private $adapter;

    private $entity;

    /**
     * Adapter usado para a autenticação
     * @var DTUXFirewall\Auth\Adapter
     */

    /**
     * Construtor da classe
     *
     * @return void
     */
    public function __construct($adapter = null,$entity)
    {
        $this->adapter = $adapter;
        $this->entity = $entity;
    }

    public function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()->get('DTUXFirewall\Storage\FirewallStorage');
        }

        return $this->storage;
    }

    public function getAdapter()
    {

        return $this->adapter;
    }

    /**
     * Faz a autenticação dos usuários
     *
     * @param array $params
     * @return array
     */
    public function authenticate($params)
    {
        if (!isset($params['email']) || !isset($params['password'])) {
            throw new \Exception("Parâmetros inválidos");
        }

        // Criando Storage para gravar sessão da authtenticação
        //$sessionStorage = new SessionStorage("DTuX");
        $auth = new AuthenticationService();
        // Criando Storage para gravar sessão da authtenticação
        $session = $this->getServiceLocator()->get('Session');

        if (array_key_exists('rememberme',$params)) {
            $this->getSessionStorage()->setRememberMe(1);
            $auth->setStorage($this->getSessionStorage());
        }
        $auth->setStorage($session); // Definindo o SessionStorage para a auth

        $this->getAdapter()->setEntity($this->entity);
        $this->getAdapter()->setUsername($params['email']);
        $this->getAdapter()->setPassword($params['password']);

        $result = $auth->authenticate($this->getAdapter());

        if (! $result->isValid()) {
            return false;
        }

        //salva o user na sessão
        $identity = $auth->getIdentity()["credenciais"];

        $session->write(
                array('credenciais' => $identity,
                      'privilegios' => $identity->getPrivilegios(),
                      'user_id' => $identity->getId(),
                      'ip_address' => $_SERVER['REMOTE_ADDR'],
                      'user_agent'    => $_SERVER['HTTP_USER_AGENT'])
            );

        return true;
    }

    /**
     * Faz o logout do sistema
     *
     * @return void
     */
    public function logout() {
        $session = $this->getServiceLocator()->get('Session');
        $auth = new AuthenticationService;
        $auth->setStorage($session);
        $auth->clearIdentity();
        return true;
    }

    /**
     * Faz a autorização do usuário para acessar o
     * @return boolean
     */
    public function authorize()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            return true;
        }
        return false;
    }

}