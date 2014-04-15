 DTUXFirewall
==============

Módulo de autenticação e autorização via Zend\Acl
Conteúdo:
- Auth/Adapter - Adaptador para autenticação.
- Controller/AuthController - Controller padrão, basta ser extendido.
- Controller/Plugin/FirewallPlugin - Plugin Controller que controla as requisições via acl.
- Document - Modelo para entidades que utilizam ODM.
- Service - Serviços necessários que são utilizado pelo controller.
- View - Helpers que auxiliam as views.
- Storage - Meio de armazenamento de informações na sessao

Obs.: A única camada que possui conectividade com banco de dados, é camada "Service".

Próximas implementações:
- View Helper.
- Validação da informações na Service.
