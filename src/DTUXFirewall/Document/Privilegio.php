<?php

namespace DTUXFirewall\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Stdlib\Hydrator;
use Doctrine\Common\Collections\ArrayCollection;

use DTUXBase\Document\AbstractDocument as AbstractDocument;


/**
 * Privilegio - Essa entidade representa o privilégio (a permissão) para realizar uma ou mais ações na plataforma
 *
 * @ODM\Document(
 *     collection="Privilegio"
 * ),
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\HasLifecycleCallbacks
 */
class Privilegio extends AbstractDocument
{


    const PRIVILEGIO_LIBERADO = 'allow';
    const PRIVILEGIO_BLOQUEADO = 'deny';

   /**
     * @ODM\Field(type="string")
    */
    protected $descricao; // Administrador

    /**
     * @ODM\EmbedOne(targetDocument="Resource")
     */
    protected $resource; // Index,

    /**
     * @ODM\EmbedMany(targetDocument="Rule")
     */
    protected $rule; // View

    /**
     * @ODM\String
     */
    protected $tipo = self::PRIVILEGIO_LIBERADO;

    public function __construct(array $options = array())
    {

        $this->rule = new \Doctrine\Common\Collections\ArrayCollection();
        (new Hydrator\ClassMethods)->hydrate($options,$this);

    }


    /**
     * Adiciona um recurso para um privilegio
     * @param $resource
     */
    public function adicionarRule($rule)
    {
        if($rule instanceof Rule)
        {
            $this->rule->add($rule);

        } else {

            $elemento = new Rule();
            $elemento->setNome($rule);
            $this->rule->add($rule);
        }

    }

     /**
     * Adiciona um recurso para um privilegio
     * @param $resource
     */
    public function adicionarResource($resource)
    {
        if($resource instanceof Resource)
        {
            $this->resource = $resource;

        }

    }

    public function getRules()
    {
        $rules = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->getRule() as $rule){
            $rules[] = $rule;
        }
        return $rules;
    }

    public function getRuleExiste($rule)
    {

        $funcao =
            function($element) use ($rule) {

                if($rule === $element || $element->getNome() === $rule ) {
                    return true;
                }

                return false;
            }
        ;

        $ret = $this->rule->filter($funcao);
        return $ret->isEmpty() ? null : $ret->first();
    }

    public function removerRule($rule)
    {
        if($obj = $this->getRuleExiste($rule))
            $this->getRule()->removeElement($obj);
        else
            throw new \RuntimeException("Não é possível remover um rule porque não foi encontrado.", 1);
    }

 /**
     * Obtem os tipos possíveis
     *
     */
    public static function getTipoPossiveis($associativo = false)
    {
        $tipos = array(
            self::PRIVILEGIO_LIBERADO  => 'Liberado',
            self::PRIVILEGIO_BLOQUEADO => 'Bloqueado'
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

    /**
     * @param mixed $descricao
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * @return mixed
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @param mixed $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $rule
     */
    public function setRule($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->rule;
    }



}