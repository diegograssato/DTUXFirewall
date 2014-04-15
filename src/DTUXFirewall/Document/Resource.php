<?php

namespace DTUXFirewall\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Stdlib\Hydrator;
use Doctrine\Common\Collections\ArrayCollection;
use DTUXBase\Document\AbstractDocument as AbstractDocument;

/**
 * Resource
 *
 * @ODM\EmbeddedDocument
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\HasLifecycleCallbacks
 */
class Resource extends AbstractDocument
{
    public function __construct($options = array())
    {
        (new Hydrator\ClassMethods)->hydrate($options,$this);
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");

    }

    public function __toString()
    {
        return $this->getNome() ? $this->getNome() : $this->getId();
    }

    const ROLE_ROTA = 'rota';
    const ROLE_CONTROLLER = 'controller';

    /**
     * @ODM\String
     */
    protected $tipo = self::ROLE_CONTROLLER;



    /**
     * Obtem os tipos possíveis
     *
     */
    public static function getTipoPossiveis($associativo = false)
    {
        $tipos = array(
            self::ROLE_ROTA  => 'ROTA',
            self::ROLE_CONTROLLER => 'CONTROLLER'
        );

        return $associativo === true ? $tipos : array_keys($tipos);
    }

    /**
     * Restorna o tipo por extenso
     * @return self::TIPo
     */
    public function getTipoPorExtenso()
    {
        $tipos = self::getTipoPossiveis( true );

        $resp = null;

        if ( array_key_exists($this->getTipo(), $tipos) ) {
            $tipo = $tipos[ $this->getTipo() ];
        }

        return $tipo;
    }

    /**
     * @param string $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }


}
