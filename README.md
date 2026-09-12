# Oryon Gestao API - Desafio Tecnico Backend

API RESTful desenvolvida em **PHP 8.2+** e **Laravel 11** para o modulo de gestao de departamentos, colaboradores e movimentacao estrategica de equipes.

O projeto foi desenhado com foco em clareza arquitetural, integridade transacional de dados, cache inteligente, autenticacao stateless via JWT e cobertura completa de testes automatizados.

---

## 🛠️ Stack Tecnologica

* **Linguagem:** PHP 8.2+
* **Framework:** Laravel 11
* **Banco de Dados Principal:** PostgreSQL 16
* **Cache / In-Memory:** Redis 7 / Cache de Aplicacao
* **Autenticacao:** JWT (JSON Web Tokens via `php-open-source-saver/jwt-auth`)
* **Documentacao Interativa:** Swagger / OpenAPI 3.0 (`l5-swagger`)
* **Testes Automatizados:** PHPUnit 10 / Pest
* **Conteinerizacao:** Docker & Docker Compose

---

## 🚀 Como Executar o Projeto

Voce pode rodar a aplicacao utilizando **Docker** (recomendado) ou **Localmente**.

### Opcao 1: Execucao com Docker (Recomendado)

1. **Clone o repositorio e acesse a pasta:**
   ```bash
   git clone <url-do-repositorio>
   cd oryon-backend-api
   ```

2. **Crie o arquivo de ambiente `.env`:**
   ```bash
   cp .env.example .env
   ```

3. **Suba os containers da aplicacao:**
   ```bash
   docker compose up -d --build
   ```

4. **Gere a chave da aplicacao e a chave secreta do JWT:**
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan jwt:secret --force
   ```

5. **Execute as migrations e popule o banco com a massa de dados inicial:**
   ```bash
   docker compose exec app php artisan migrate --seed
   ```

A API estara disponivel em: `http://localhost:8000`

---

### Opcao 2: Execucao Local (Sem Docker)

1. **Instale as dependencias do Composer:**
   ```bash
   composer install
   ```

2. **Configure o arquivo `.env`:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret --force
   ```
   *Ajuste as variaveis `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` para apontar para sua instancia local do PostgreSQL.*

3. **Execute as migrations e seeders:**
   ```bash
   php artisan migrate --seed
   ```

4. **Inicie o servidor embutido:**
   ```bash
   php artisan serve
   ```

---

## 🔐 Autenticacao na API

As rotas da aplicacao (exceto o login) sao protegidas por tokens **JWT**.

### 1. Obter Token de Acesso (Login)
* **Endpoint:** `POST /api/auth/login`
* **Credenciais padrao (geradas no Seeder):**
  * **E-mail:** `admin@oryon.com.br`
  * **Senha:** `senha123`
* **Exemplo de Requisicao:**
  ```json
  {
    "email": "admin@oryon.com.br",
    "password": "senha123"
  }
  ```
* **Exemplo de Resposta (HTTP 200):**
  ```json
  {
    "token_de_acesso": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "tipo_token": "bearer",
    "expira_em_segundos": 3600,
    "usuario": {
      "id": 1,
      "name": "Jorge Carvalho Admin",
      "email": "admin@oryon.com.br"
    }
  }
  ```

### 2. Utilizacao nas Rotas Protegidas
Envie o token no cabecalho HTTP de cada requisicao:
```http
Authorization: Bearer <seu_token_jwt_aqui>
Accept: application/json
```

---

## 📖 Documentacao da API (Swagger / OpenAPI)

A aplicacao conta com interface interativa do Swagger contendo a descricao dos endpoints, schemas de body, parametros de paginacao/filtro e exemplos reais de resposta.

* **URL de Acesso:** [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

> **Como autenticar no Swagger:**
> 1. Realize login no endpoint `/api/auth/login` e copie o `token_de_acesso`.
> 2. Clique no botao **Authorize** (cadeado verde no canto superior direito).
> 3. Cole o token e clique em **Authorize**.

---

## 📮 Collection do Postman

Para facilitar os testes manuais e a avaliacao da banca, o arquivo [`postman_collection.json`](postman_collection.json) esta localizado na raiz do projeto.

### Como importar e utilizar:
1. Abra o Postman e clique em **Import** -> Selecione o arquivo `postman_collection.json`.
2. A collection possui a variavel `{{base_url}}` predefinida como `http://localhost:8000/api`.
3. Execute a requisicao `01. Autenticacao > 01. Login (Obter Token JWT)`.
4. Um script automatico de teste ira capturar o token retornado e preencher a variavel `{{token}}` da collection, habilitando imediatamente todas as outras chamadas.

### Estrutura das Pastas no Postman:
* **01. Autenticacao:** Login, Me, Refresh Token, Logout.
* **02. Departamentos:** CRUD completo, paginacao, filtro por status e teste do bloqueio de exclusao de departamentos com colaboradores ativos (HTTP 422).
* **03. Colaboradores:** CRUD completo, paginacao, filtros combinados (`department_id` + `status`) e inativacao via DELETE (soft delete).
* **04. Transferencia de Colaboradores:** Transferencia em lote com sucesso, validacao de mesmo departamento e destino inexistente.

---

## 🧪 Testes Automatizados

A aplicacao possui **100% de cobertura dos cenarios de negocio e regras de validacao** exigidos no edital.

### Como Executar os Testes:

```bash
# Execucao via Docker
docker compose exec app php artisan test

# Execucao Local
php artisan test
```

Os testes utilizam banco em memoria SQLite com a trait `RefreshDatabase`, executando toda a suite de forma rapida e isolada.

### Principais Cenarios Cobertos:
* **Departamentos (`DepartmentTest.php`):** Criacao valida, validacao de campos invalidos, exibicao detalhada, atualizacao, **bloqueio de exclusao com colaboradores ativos (422)**, exclusao permitida sem colaboradores ativos (204), e listagem paginada com filtro de status.
* **Colaboradores (`EmployeeTest.php`):** Criacao valida, campos obrigatorios, **validacao de e-mail unico**, validacao de chave estrangeira existente, validacao de valores permitidos para status, exibicao, atualizacao, **inativacao com soft delete via DELETE**, e filtros combinados.
* **Transferencia (`TransferEmployeesTest.php`):** **Transferencia exclusiva de colaboradores ativos**, nao transferencia de colaboradores inativos, retorno da quantidade transferida, impedimento de transferencia para a mesma origem, validacao de origem/destino inexistentes, e **garantia de rollback transacional em caso de falha**.
* **Autenticacao & Seguranca (`AuthTest.php`):** Ciclo completo de login, credenciais invalidas, bloqueio sem token, perfil e logout.
* **Cache & Policies (`CacheAndPolicyTest.php`):** Armazenamento em cache e **invalidacao reativa automatica** em mutacoes e transferencias.

---

## 🏛️ Decisoes Tecnicas e Arquiteturais

1. **Arquitetura em Camadas (Separacao de Responsabilidades):**
   * **Controllers Enxutos:** Responsaveis apenas por receber a requisicao, invocar o servico correspondente e retornar o Resource formatado.
   * **Form Requests Dedicados:** Isolam regras de validacao de entrada e formatacao de mensagens fora dos Controllers.
   * **Camada de Servicos (`Services/`):** Centraliza as regras de negocio e logica de persistencia, facilitando reaproveitamento e testes unitarios.
   * **API Resources (`Resources/`):** Desacoplam a estrutura do banco de dados da saida JSON da API, permitindo carregamento condicional eficiente de relacionamentos (`whenLoaded`).

2. **Integridade Transacional e Atomicidade (`DB::transaction`):**
   * O endpoint de transferencia de colaboradores executa todas as movimentacoes de forma atomica dentro de uma transacao de banco de dados. Caso qualquer excecao seja disparada durante o processo, o banco executa o rollback automatico, garantindo consistencia total.

3. **Cache Inteligente com Invalidacao Reativa:**
   * A listagem de departamentos utiliza estrategia de **versionamento de cache** em O(1), gravando consultas paginadas e invalidando automaticamente todas as versoes em qualquer operacao de escrita (criacao, edicao, exclusao ou transferencia).

4. **Tratamento Global de Excecoes Humanizado:**
   * No `bootstrap/app.php`, excecoes de Dominio (`DomainException`), Validacao (`ValidationException`), Modelos (`ModelNotFoundException`) e Autenticacao (`AuthenticationException`) sao interceptadas e convertidas em respostas JSON semanticas e legiveis em portugues.

5. **Soft Deletes:**
   * A remocao de colaboradores atraves do endpoint `DELETE /api/employees/{id}` marca o status como `inativo` e preenche a coluna `deleted_at`, mantendo o historico preservado no banco para fins de auditoria.

---

## 🔮 O Que Faria Diferente ou Melhoraria com Mais Tempo

* **Processamento Assincrono (Queues / RabbitMQ):** Para bases corporativas com dezenas de milhares de colaboradores, a transferencia entre departamentos poderia ser despachada para uma fila assincrona com notificacao de progresso via WebSockets ou Webhook.
* **Observabilidade e Metricas:** Integracao com OpenTelemetry, Prometheus e Grafana para rastreamento distribuido de requisicoes e metricas de tempo de resposta.
* **Analise Estatica Avancada:** Adicao de pipeline de CI/CD via GitHub Actions executando PHPStan (nivel 8) e Laravel Pint para garantia continua de padrao de codigo.
