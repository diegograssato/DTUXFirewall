<?php

namespace DTUXFirewall\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Stdlib\Hydrator;
use Doctrine\Common\Collections\ArrayCollection,
    Zend\Form\Annotation as ZFA;
use DTUXBase\Document\AbstractDocument as AbstractDocument;
/** Apenas string a serem utilizadas nas roles(excluir, edita, salvar, cadastrar */

/**
 * Perfil
 *
 * @ODM\Document(
 *     collection="Perfil"
 * ),
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\HasLifecycleCallbacks
 */
class Perfil extends AbstractDocument
{
    public function __construct(array $options = array())
    {
        $this->privilegio = new \Doctrine\Common\Collections\ArrayCollection();
        (new Hydrator\ClassMethods)->hydrate($options,$this);
    }
    public function __toString()
    {
        return $this->getNome() ? $this->getNome() : $this->getId();
    }


    /**
     * @ODM\ReferenceMany(targetDocument="Privilegio")
     */
    protected $privilegio;

    /**
     * @ODM\ReferenceOne(targetDocument="Perfil")
     */
    protected $parent;

    /**
     * @ODM\Hash
     */
    protected $meta;


     /**
     * Adiciona um privilegio a um grupo
     * @param $resource
     */
    public function adicionarPrivilegio($privilegio)
    {
        if($privilegio instanceof Privilegio)
        {
            $this->privilegio[] = $privilegio;

        } else {

            $elemento = new Privilegio();
            $elemento->setNome($privilegio);
            $this->privilegio[] = $privilegio;
        }

    }

    public function getPrivilegios()
    {
        $privilegios = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->getPrivilegio() as $privilegio){

           if(! $this->getPrivilegioExiste( $privilegio) ) {

                      $privilegios[] =  $privilegio ;
            }
        }

        return $privilegios;
    }

    public function getPrivilegioExiste($privilegio)
    {

        $funcao =
            function($element) use ($privilegio) {

                if($privilegio === $element || $element->getNome() === $privilegio ) {
                    return true;
                }

                return false;
            }
        ;

        $ret = $this->privilegio->filter($funcao);
        return $ret->isEmpty() ? null : $ret->first();
    }

    public function removerPrivilegio($privilegio)
    {
        if($obj = $this->getRuleExiste($privilegio))
            $this->getPrivilegio()->removeElement($obj);
        else
            throw new \RuntimeException("Não é possível remover um privilegio porque não foi encontrado.", 1);
    }



    /*public function toArray()
    {
        /// Se o pai dele existir
        if(isset($this->parent))
            // Eu pego ID do pai
            $parent = $this->parent->getId();
        else
            $parent = false;

        return array(
            'id' => $this->id,
            'nome' => $this->nome,
            'parent' => $parent
        );
    }*/

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;

    }


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



     /**
     * Gets the value of privilegio.
     *
     * @return mixed
     */
    public function getPrivilegio()
    {
        return $this->privilegio;
    }

    /**
     * Sets the value of privilegio.
     *
     * @param mixed $privilegio the privilegio
     *
     * @return self
     */
    public function setPrivilegio($privilegio)
    {
        $this->privilegio = $privilegio;

        return $this;
    }
}