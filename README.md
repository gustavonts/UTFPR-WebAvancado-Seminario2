# Web Avançado - Sistema de Gestão de Pedidos

Este projeto é uma aplicação web em Symfony para gestão de pedidos de e-commerce, com autenticação de usuários, painel administrativo, cadastro de pedidos, controle de status e envio de notificações por e-mail.

## O que a aplicação faz

A aplicação permite:

- cadastrar e autenticar usuários;
- acessar um dashboard com resumo de pedidos e valores;
- criar, visualizar e gerir pedidos;
- adicionar produtos aos pedidos;
- acompanhar o histórico de status do pedido;
- anexar documentos aos pedidos;
- enviar e-mails de atualização de status para o cliente.

## Tecnologias utilizadas

- PHP 8.4
- Symfony 8
- Doctrine ORM
- Twig
- PostgreSQL
- Symfony Security
- Symfony Forms
- Symfony Mailer

## Estrutura principal do projeto

- src/Controller: controladores das páginas e ações da aplicação
- src/Entity: entidades do domínio, como Pedido, Produto, Categoria, Usuário e histórico de status
- src/Form: formulários de cadastro e gestão
- src/Repository: consultas e acesso aos dados
- src/Service: serviços auxiliares, como envio de e-mail
- templates: páginas Twig do frontend
- migrations: versões do banco de dados
- config: configuração do Symfony, rotas, segurança e Doctrine

## Requisitos

- PHP 8.4+
- Composer

## Configuração local

1. Instale as dependências:

```bash
composer install
```

2. Configure a conexão com o banco no arquivo .env ou em um arquivo .env.local.

Exemplo de configuração para PostgreSQL:

```env
DATABASE_URL="postgresql://postgres:postgres@127.0.0.1:5432/webavancado?serverVersion=17&charset=utf8"
```

```

3. Execute as migrações:

```bash
php bin/console doctrine:migrations:migrate
```

4. Inicie a aplicação:

```bash
php -S 127.0.0.1:8000 -t public
```

Ou, se estiver usando o Symfony server:

```bash
symfony server:start
```

## Banco de dados

O projeto utiliza PostgreSQL como banco principal.

- Container Docker: database
- Arquivo de configuração: .env
- Migrações: pasta migrations/

Para visualizar ou aplicar alterações no banco, use:

```bash
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:migrate
```

## E-mails

O envio de e-mail é feito com Symfony Mailer.

No ambiente local, o projeto pode usar o Mailpit via Docker, acessível em:

- http://localhost:8025

## Fluxo principal

1. O usuário acessa a aplicação e faz login.
2. O dashboard exibe um resumo geral dos pedidos.
3. No módulo de pedidos, é possível criar novos pedidos e adicionar produtos.
4. O status do pedido pode ser alterado e isso gera um histórico.
5. Um e-mail é enviado ao cliente quando o status é atualizado.

## Comandos úteis

```bash
php bin/console debug:router
php bin/console doctrine:migrations:diff
php bin/console lint:container
```

## Observação

A aplicação foi desenvolvida como um projeto acadêmico/experimental de desenvolvimento web avançado com Symfony e integra conceitos de MVC, ORM, autenticação, formulários e envio de e-mails.
