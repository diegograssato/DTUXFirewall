<?php

namespace DTUXFirewall\Document\Repository;

use \DTUXBase\Document\AbstractDocumentRepository as AbstractDocumentRepository;

class UsuarioRepository extends AbstractDocumentRepository
{

    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder()
            ->sort('nome', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * Método utilizado buscar os emails de um usuário.
     *
     * @param  string $email username ou e-mail do usuário que está tentando autenticar-se.
     *
     * @return Usuario retorna uma instância de Usuario.
     */
    public function buscaCredenciais($email, $executar = true)
    {
        $qb = $this->createQueryBuilder();
        $qb->addOr( $qb->expr()
                      ->field('emails.email')
                      ->equals( $email )
                      ->field('emails.ativo')
                      ->equals(true)
            )
            ->addOr( $qb->expr()
                      ->field('username')->equals( $email )
        );

        return $executar === true ? $qb->getQuery()->getSingleResult() : $qb;
    }

    /**
     * Valida as credenciais de um usuário
     * @param  [type] $usuario Usename|Email
     * @param  [type] $senha   Senha
     * @return [type]          Usuario|false;
     */
    public function validaCredenciais($usuario, $senha)
    {

        $credenciais = $this->buscaCredenciais($usuario);

        if( $credenciais instanceof \DTUXFirewall\Document\Usuario )
        {
            $hashSenha = $credenciais->encryptPassword($senha);

            if( $hashSenha  === $credenciais->getPassword() )
            {
                return $credenciais;

            } else {
                return false;
            }

        } else {

            return false;
        }
    }



}
