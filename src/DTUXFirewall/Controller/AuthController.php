<?php

namespace DTUXFirewall\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Stdlib\Hydrator;
use SpiffyRoutes\Annotation as Route;

/**
 * @Route\Root("/auth")
 */
class AuthController extends AbstractActionController
{
    /**
     * Contrutor padrão
     * @todo Declaração de todas variaveis
      */
    public function __construct()
    {

        $this->entity = "\DTUXFirewall\Document\Usuario";
        $this->authService = "DTUXFirewall\Service\Auth";
        $this->aclService = "DTUXAcl";
        $this->storageService = 'DTUXFirewall\Storage\FirewallStorage';
        $this->controller = "auth";
        $this->route = "dtux-firewall-login";

    }

    /**
     * @Route\Segment("/captcha[/:id]", constraints={"id"="[a-zA-Z][a-zA-Z0-9_-]*"})
     */
    public function generateAction()
    {
       // echo "To";
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', "image/png");

        $id = $this->params('id', false);

        if ( null != $id) {

           $image = './data/captcha/' . $id;

            if ( file_exists( $image ) !== false) {
                $imagegetcontent = @file_get_contents( $image );

                $response->setStatusCode(200);
                $response->setContent( $imagegetcontent );

                if ( file_exists( $image ) == true ) {
                    unlink( $image );
                }
            }

        }

        return $response;
    }

    public function getSessionStorage()
    {
        if (is_null( $this->storage ))
            $this->storage = $this->getServiceLocator()->get( $this->storageService );

        return $this->storage;
    }

    /**
     * @Route\Segment("/login", name="dtux-firewall-login")
     */
    public function loginAction()
    {
        $form = new \DTUXFirewall\Form\Login("Login", $this->getRequest()->getBaseUrl().'/auth/captcha');
        $request = $this->getRequest();

        if ( $request->isPost() )
        {
            $form->setData( $request->getPost() );

            if( $form->isValid() ) {

                $data = $request->getPost()->toArray();

                $service = $this->getServiceLocator()->get( $this->authService );
                $auth = $service->authenticate($data);

                if(  $auth  ) {
                    $entity = $this->getServiceLocator()->get( $this->aclService );

                    if( $entity->getCredenciais()->getActive() ){

                        $this->flashMessenger()->setNamespace('info')->addMessage('Usuário logado com sucesso!');
                        return $this->redirect()->toRoute('home');
                    }else{

                      $service->logout() ;
                      $this->flashMessenger()->setNamespace('danger')->addMessage('Seu cadastro está inativo, favor verificar seu e-mail');
                      return $this->redirect()->toRoute('dtux-firewall-login');
                  }

                } else {

                    $this->flashMessenger()->setNamespace('danger')->addMessage('Usuário ou senha incorretos!');
                    return $this->redirect()->toRoute('dtux-firewall-login');
                }

            }
        }

        return new ViewModel( array(
                             'form'=>$form,
                             'error'=>$error
                             )
        );

    }



    /**
     * @Route\Literal("/logout", name="dtux-firewall-logout")
     */
    public function logoutAction()
    {
        $service = $this->getServiceLocator() ->get( $this->authService );

        if ( $service->logout() ) {
                $this->flashMessenger()->setNamespace('danger')->addMessage('Sessão encerrada!');
                return $this->redirect()->toRoute('dtux-firewall-login');
        }
    }
}
