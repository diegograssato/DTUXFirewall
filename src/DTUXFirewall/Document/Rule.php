<?php

namespace DTUXFirewall\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Stdlib\Hydrator;
use Doctrine\Common\Collections\ArrayCollection,
    Zend\Form\Annotation as ZFA;
use DTUXBase\Document\AbstractDocument as AbstractDocument;
/** Apenas string a serem utilizadas nas roles(excluir, edita, salvar, cadastrar */

/**
 * Rule
 *
 * @ODM\EmbeddedDocument
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\HasLifecycleCallbacks
 */
class Rule extends AbstractDocument
{
    public function __construct(array $options = array())
    {
        (new Hydrator\ClassMethods)->hydrate($options,$this);
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }
    public function __toString()
    {
        return $this->getNome() ? $this->getNome() : $this->getId();
    }


    /**
     * @ODM\Hash
     */
    protected $meta;

    /**
     * Gets the Array associativo utilizado para definir metadados no evento..
     *
     * @return array
     */
    public function getMeta($chave = null)
    {
        if(null === $chave)
            return $this->meta;

        if(array_key_exists($chave, $this->meta))
            return $this->meta[$chave];

        return null;
    }

    /**
     * Sets the Array associativo utilizado para definir metadados no evento..
     *
     * @param array $meta the meta
     *
     * @return self
     */
    public function setMeta($valor, $chave = null)
    {
        if(null === $chave)
            $this->meta = $valor;
        else
            $this->meta[$chave] = $valor;

        return $this;
    }
}