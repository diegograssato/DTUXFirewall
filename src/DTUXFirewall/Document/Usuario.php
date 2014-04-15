<?php
namespace DTUXFirewall\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zend\Stdlib\Hydrator;
use Zend\Math\Rand,
    Zend\Crypt\Key\Derivation\Pbkdf2,
    Doctrine\Common\Collections\ArrayCollection;

use DTUXBase\Document\AbstractDocument as AbstractDocument;
use Zend\Form\Annotation as ZFA;


/**
 * Usuario
 *
 * @ODM\Document(
 *     collection="Usuario",
 *     repositoryClass="DTUXFirewall\Document\Repository\UsuarioRepository"
 * ),
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\UniqueIndex({
 *   @ODM\UniqueIndex(keys={"active"="asc"}),
 *   @ODM\UniqueIndex(keys={"activationKey"="asc"})
 * })
 * @ODM\HasLifecycleCallbacks
 */
class Usuario extends AbstractDocument
{
    public function __construct(array $options = array())
    {

        $this->emails = new \Doctrine\Common\Collections\ArrayCollection();
        //$this->perfil = new \Doctrine\Common\Collections\ArrayCollection();
        $this->privilegio = new \Doctrine\Common\Collections\ArrayCollection();
        (new Hydrator\ClassMethods)->hydrate($options,$this);

    }

   /* public function __clone() {
        $this->id = null;
        $this->emails = new \Doctrine\Common\Collections\ArrayCollection();
    }*/

    public function __toString() {
        return $this->nome ? $this->nome : $this->username;
    }

    /**
     * @ODM\ReferenceOne(targetDocument="Perfil")
     */
    protected $perfil;

    /**
     * @ODM\ReferenceMany(targetDocument="Privilegio")
     */
    protected $privilegio;

     /**
     * Adiciona um recurso para um usuario
     * @param  $perfil
     */
    public function adicionaPerfil( $perfil)
    {
        if( $perfil instanceof Perfil)
        {
             $this->perfil = $perfil;

        }

    }

     /**
     * Adiciona um privilegio a um usuario
     * @param $resource
     */
    public function adicionarPrivilegio($privilegio)
    {
        if($privilegio instanceof Privilegio)
        {
            $this->privilegio[] = $privilegio;

        }
    }

    public function getPrivilegiosExiste($privilegio)
    {

        $funcao =
            function($element) use ($privilegio) {

                if($privilegio === $element || $element->getNome() === $privilegio->getNome() ) {
                    return true;
                    //echo "OK";
                }

                return false;
            }
        ;

        $ret = $this->privilegio->filter($funcao);
        return $ret->isEmpty() ? null : $ret->first();
    }

    public function getPrivilegios(){
        $privilegios = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->getPrivilegio() as $usuarioPrivilegio){
            $privilegios[] =  $usuarioPrivilegio ;
        }

        if( NULL != $this->getPerfil() ){
            foreach($this->getPerfil()->getPrivilegios() as $perfilPrivilegios){
                if(! $this->getPrivilegiosExiste( $perfilPrivilegios) )
                      $privilegios[] =  $perfilPrivilegios ;

            }

       }

        return $privilegios;
    }

    /**
     * @ODM\Field(type="string")
     * @ZFA\Filter({"name":"StringTrim"})
     * @ZFA\Required(true)
     * @ZFA\Validator({"name":"StringLength", "options":{"min":1, "max":25}})
     * @ZFA\Validator({"name":"Regex", "options":{"pattern":"/^[a-zA-Z][a-zA-Z0-9._-]{0,24}$/"}})
     * @ZFA\Attributes({"type":"text"})
     * @ZFA\Options({"label":"Username:"})
    */
    protected $username;

    /** @ODM\Field(type="string")
     * @ZFA\Filter({"name":"StringTrim"})
     * @ZFA\Required(true)
     * @ZFA\Validator({"name":"StringLength", "options":{"min":8}})
     * @ZFA\Attributes({"type":"text"})
     * @ZFA\Options({"label":"Password:"})
    */
    protected $password;


    /** @ODM\Field(type="string") */
    protected $salt;

    /** @ODM\Field(type="string") */
    protected $active;

    /** @ODM\Field(type="string") */
    protected $activationKey;

    /**
     * @ODM\Field(type="boolean", name="loginAtivo")
     * @var boolean
     */
    protected $loginAtivo;

    /**
     * @ODM\Field(type="boolean", name="is_admin")
     * @var boolean
     */
    private $admin;


     /**
      * @ODM\EmbedMany(targetDocument="UsuarioEmail")
     */
    protected $emails;



    /**
     * @ODM\ReferenceOne(targetDocument="SimpleAdmin\Document\Empresa")
     * @ZFA\Type("DoctrineORMModule\Form\Element\EntitySelect")
     * @ZFA\Options({"label":"Empresa:", "target_class":"SimpleAdmin\Document\Empresa"})
     * @ZFA\Attributes({"multiple":true})
     */
    protected $empresa;


    /**
     * @ZFA\Required(false)
     * @ZFA\Type("Zend\Form\Element\File")
     * @ZFA\Validator({"name":"Zend\Validator\File\UploadFile"})
     * @ZFA\Filter({"name":"Zend\Filter\File\RenameUpload", "options":{"target":"./data/usuario_", "use_upload_extension":"true", "randomize":"true"}})
     * @ZFA\Validator({"name":"Zend\Validator\File\Size", "options":{"min":"1kB", "max":"2MB"}})
     * @ZFA\Validator({"name":"Zend\Validator\File\MimeType", "options":{"image/gif", "image/jpg", "image/png", "enableHeaderCheck":"true"}})
     * @ZFA\Attributes({"class":"form-control"})
     * @ZFA\Options({"label":"Foto:"})
     * @ODM\Field(type="string", name="avatar")
     * @var $avatar string
     */
    protected $avatar;

    /** @ODM\Field(type="hash") */
    protected $meta;

    /** @ODM\Field(type="hash") */
    protected $acl;



    public function setPassword($password) {
       $this->password = $this->encryptPassword($password);
       return $this;
    }

    public function encryptPassword($password)
    {
        if( ! is_string( $this->salt ) ) {
            $this->salt = md5( Rand::getBytes( strlen( $this->getEmailPrincipal().$this->salt ), true ) );
            $this->activationKey = md5( $this->getEmailPrincipal().$this->salt );
        }

        return md5( Pbkdf2::calc( 'sha256', $password, $this->salt, 10000, strlen( $password*2 ) ) );
    }

    public function adicionarEmail($email, $tornarPrincipal = false)
    {
        $emailObject = new UsuarioEmail();
        $emailObject->setEmail( $email );
        $emailObject->setUsuario( $this );

        // se for o primeiro e-mail a ser adicionado, ele deve se tornar o principal.
        $primeiroEmail = false;
        if(count($this->getEmails()) == 0)
           $primeiroEmail = true;

        $this->emails->add($emailObject);

        if($tornarPrincipal || $primeiroEmail)
            $this->tornarEmailPrincipal($email);

        return $this;
    }


    public function removerEmail($email)
    {
        if($email instanceof UsuarioEmail)
            $emailString = $email->getEmails();
        else
            $emailString = $email;

        if($emailString == $this->getEmailPrincipal()->getEmails()) {

            throw new \RuntimeException("Não é possível remover o e-mail principal.", 1);

        } else {
            if($obj = $this->getEmailSeExistir($emailString))
                $this->emails->removeElement($obj);
            else
                throw new \RuntimeException("Não é possível remover o e-mail porque não foi encontrado.", 1);
        }

        return $this;
    }

    public function possuiEmail($email)
    {
        $funcao =
            function($key, $element) use ($email)
            {

                if($element->getEmails() === $email) {
                    return true;
                }

                return false;
            }
        ;

        return $this->emails->exists($funcao);
    }


    public function getEmailPrincipal($retornaArrayString = false)
    {
        $funcao =
            function($element) {

                if($element->isPrincipal()) {
                     return true;
                }

                return false;
            }
        ;

        $ret = $this->emails->filter($funcao)->first();

        return $retornaArrayString === false ? $ret : $ret->getEmails();
    }

    public function tornarEmailPrincipal($email)
    {
        if($email instanceof UsuarioEmail)
            $emailString = $email->getEmails();
        else
            $emailString = $email;

        $emailObject = $this->getEmailSeExistir($emailString);


        if($emailObject == null)
            throw new \RuntimeException("Não é possível tornar principal um e-mail que não foi previamente cadastrado.", 1);

        $emailPrincipalAnterior = $this->getEmailPrincipal();
        // se houver um e-mail principal anterior
        if($emailPrincipalAnterior != null)
            $emailPrincipalAnterior->setPrincipal(false);

        $emailObject->setPrincipal(true);

        return $this;
    }

    /**
     * Dado um endereço de e-mail, retorna uma instância de UsuarioEmail, se existir (e estiver associada ao usuario).
     *
     * DICA: É recomendado o uso dessa função quando precisamos verificar se um e-mail existe, e caso exista, retorá-lo.
     *     Isso ocorre porque ela caminha pelo array somente uma vez.
     *
     * @param string $email a ser localizado e retornado
     * @return UsuarioEmail|null se existir retorna a instância UsuarioEmail, senão retorna NULL.
     */
    public function getEmailSeExistir($email)
    {
        $funcao =
            function($element) use ($email) {

                if($email === $element || $element->getEmail() === $email) {
                    return true;
                }

                return false;
            }
        ;

        $ret = $this->emails->filter($funcao);

        return $ret->isEmpty() ? null : $ret->first();
    }

    /**
    * GET AND SETTER
    */

    /**
     * Gets the value of username.
     *
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the value of username.
     *
     * @param mixed $username the username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the value of password.
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * Gets the value of salt.
     *
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Sets the value of salt.
     *
     * @param mixed $salt the salt
     *
     * @return self
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Gets the value of active.
     *
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Sets the value of active.
     *
     * @param mixed $active the active
     *
     * @return self
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Gets the value of activationKey.
     *
     * @return mixed
     */
    public function getActivationKey()
    {
        return $this->activationKey;
    }

    /**
     * Sets the value of activationKey.
     *
     * @param mixed $activationKey the activation key
     *
     * @return self
     */
    public function setActivationKey($activationKey)
    {
        $this->activationKey = $activationKey;

        return $this;
    }

    /**
     * Gets the value of loginAtivo.
     *
     * @return boolean
     */
    public function getLoginAtivo()
    {
        return $this->loginAtivo;
    }

    /**
     * Sets the value of loginAtivo.
     *
     * @param boolean $loginAtivo the login ativo
     *
     * @return self
     */
    public function setLoginAtivo($loginAtivo)
    {
        $this->loginAtivo = $loginAtivo;

        return $this;
    }

    /**
     * Gets the value of admin.
     *
     * @return boolean
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Sets the value of admin.
     *
     * @param boolean $admin the admin
     *
     * @return self
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Gets the value of emails.
     *
     * @return  ArrayCollection coleção de e-mails do usuário.
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Sets the value of emails.
     *
     * @param  ArrayCollection coleção de e-mails do usuário. $emails the emails
     *
     * @return self
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Gets the value of avatar.
     *
     * @return $avatar string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Sets the value of avatar.
     *
     * @param $avatar string $avatar the avatar
     *
     * @return self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Gets the value of fone.
     *
     * @return boolean
     */
    public function getFone()
    {
        return $this->fone;
    }

    /**
     * Sets the value of fone.
     *
     * @param boolean $fone the fone
     *
     * @return self
     */
    public function setFone($fone)
    {
        $this->fone = $fone;

        return $this;
    }

    /**
     * Gets the value of empresa.
     *
     * @return boolean
     */
    public function getEmpresa()
    {
        return $this->empresa;
    }

    /**
     * Sets the value of empresa.
     *
     * @param boolean $empresa the empresa
     *
     * @return self
     */
    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;

        return $this;
    }



    /**
     * Gets the value of perfil.
     *
     * @return mixed
     */
    public function getPerfil()
    {
        return $this->perfil;
    }

    /**
     * Sets the value of perfil.
     *
     * @param mixed $perfil the perfil
     *
     * @return self
     */
    public function setPerfil($perfil)
    {
        $this->perfil = $perfil;

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

    /**
     * Gets the value of meta.
     *
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Sets the value of meta.
     *
     * @param mixed $meta the meta
     *
     * @return self
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Gets the value of acl.
     *
     * @return mixed
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Sets the value of acl.
     *
     * @param mixed $acl the acl
     *
     * @return self
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;

        return $this;
    }
}

