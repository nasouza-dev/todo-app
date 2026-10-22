# 📝 To-Do App

Aplicação web de gerenciamento de tarefas desenvolvida como projeto de estudo e portfólio, utilizando Laravel no backend e Vue.js no frontend.

## ✨ Funcionalidades

- Adicionar novas tarefas.
- Listar tarefas cadastradas.
- Marcar tarefas como concluídas ou pendentes.
- Editar o título de uma tarefa.
- Excluir tarefas com confirmação.
- Validar os dados enviados à API.
- Exibir mensagens de erro e estados de carregamento.

## 🛠️ Tecnologias utilizadas

- **PHP 8.3+**
- **Laravel 12**
- **Vue.js**
- **MySQL**
- **Axios**
- **Bootstrap**
- **Vite**
- **Git e GitHub**

## ⚙️ Pré-requisitos

Antes de executar o projeto, você precisará ter instalado:

- PHP e Composer.
- Node.js e npm.
- MySQL.
- Git.

## 🚀 Como executar o projeto

### 1. Clonar o repositório

```bash
git clone git@github.com:nasouza-dev/todo-app.git
cd todo-app
```

### 2. Instalar as dependências do PHP

```bash
composer install
```

### 3. Configurar o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Configure o arquivo `.env` com os dados da sua conexão MySQL.

Crie um banco de dados chamado `todo_app`, ou utilize outro nome e atualize a configuração no `.env`.

### 4. Executar as migrations

```bash
php artisan migrate
```

### 5. Instalar as dependências do frontend

```bash
npm install
```

### 6. Iniciar a aplicação

Abra dois terminais na pasta do projeto.

**Terminal 1 — Backend Laravel:**

```bash
php artisan serve
```

**Terminal 2 — Frontend Vite:**

```bash
npm run dev
```

Acesse a aplicação pelo endereço informado pelo Laravel, normalmente:

http://127.0.0.1:8000

## 🧪 Testes automatizados

Para executar os testes:

```bash
php artisan test
```

## 📚 Objetivo do projeto

Este projeto tem como objetivo praticar conceitos de desenvolvimento web, incluindo operações CRUD, criação de APIs REST, validação de dados, integração entre frontend e backend, persistência em banco de dados e testes automatizados.

## 👩‍💻 Autora

Desenvolvido por Natalia Souza.

[GitHub](https://github.com/nasouza-dev)