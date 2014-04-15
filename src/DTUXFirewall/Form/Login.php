<?php

namespace DTUXFirewall\Form;

use Zend\Form\Form;

class Login  extends Form
{

    public function __construct($name = null, $img, $options = array()) {
        parent::__construct('Login', $options);

        $this->setAttribute('method', 'post')->setAttribute('class','form-group');;

        $email = new \Zend\Form\Element\Text("email");
       // $email->setLabel("Login: ")
        $email->setAttribute('placeholder','Entre com o login')->setAttribute('class','form-control')
                ->setAttribute('style','width: 300px;');
        $this->add($email);

        $password = new \Zend\Form\Element\Password("password");
       // $password->setLabel("Password: ")
        $password->setAttribute('placeholder','Entre com a senha')
                ->setAttribute('class','form-control')
                ->setAttribute('style','width: 300px;');
        $this->add($password);
        //pass captcha image options
        $captchaImage = new \Zend\Captcha\Image(  array(
                'font'         => $_SERVER['DOCUMENT_ROOT'].'/font/arial.ttf',
                'wordLen' => 1,
                'width' => 300,
                'height' => 90,
                'dotNoiseLevel' => 8,
                'lineNoiseLevel' => 8)
        );
        $captchaImage->setFontSize(32);
        $captchaImage->setSuffix('Otavio');
        $captchaImage->setImgDir('./data/captcha');
        $captchaImage->setImgUrl($img);

        //add captcha element...
       /*$this->add(array(
            'type' => 'Zend\Form\Element\Captcha',
            'name' => 'captcha',
            'options' => array(
              //  'label' => 'Please verify you are human',
                'captcha' => $captchaImage

            ),
            'attributes' => array(
                'class' => 'form-control',
                'style' => 'width: 300px;margin-left:86px',
                'placeholder'=>'Preencha corretamente'
            )
        ));*/
        $this->add(array(
            'name' => 'submit',
            'type'=>'Zend\Form\Element\Submit',
            'attributes' => array(
                'value'=>'Autenticar',
                'class' => 'btn btn-success'
            )
        ));


    }

}
