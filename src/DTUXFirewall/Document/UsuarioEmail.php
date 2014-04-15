<?php

namespace DTUXFirewall\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Stdlib\Hydrator;

use DTUXBase\Document\AbstractDocument as AbstractDocument;

/**
 * Representa o e-mail de um usuário (UsuarioConta).
 * @ODM\EmbeddedDocument
 */
class UsuarioEmail extends AbstractDocument
{

    /**
     * @var  Usuario dono do e-mail.
     *
     * @ODM\ReferenceOne(targetDocument="Usuario", inversedBy="emails")
     **/
    protected $usuario;

    /**
    * @ODM\Field(type="string")
    */
    protected $email;

    /**
     * @ODM\Field(type="boolean", name="principal")
     * @var boolean
     */
    protected $principal;

    /**
     * @ODM\Field(type="boolean", name="ativo")
     * @var boolean
     */
    protected $ativo;

    public function __construct(array $options = array())
    {
        /** Declaramos um objeto utilizamos um metodo */
        (new Hydrator\ClassMethods)->hydrate($options,$this);
        $this->principal = false;
        $this->ativo = true;
    }

    public function __toString()
    {
        return (string) $this->email;
    }

    /**
    * GET AND SETTER
    */

    /**
     * Gets the value of usuario.
     *
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Sets the value of usuario.
     *
     * @param mixed $usuario the usuario
     *
     * @return self
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Gets the value of email.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the value of email.
     *
     * @param mixed $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the value of principal.
     *
     * @return boolean
     */
    public function isPrincipal()
    {
        return $this->principal;
    }

    /**
     * Sets the value of principal.
     *
     * @param boolean $principal the principal
     *
     * @return self
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;

        return $this;
    }

    /**
     * Gets the value of ativo.
     *
     * @return boolean
     */
    public function getAtivo()
    {
        return $this->ativo;
    }

    /**
     * Sets the value of ativo.
     *
     * @param boolean $ativo the ativo
     *
     * @return self
     */
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;

        return $this;
    }
}