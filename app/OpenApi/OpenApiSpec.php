<?php

namespace App\OpenApi;

use OpenApi\Attributes as OAT;

#[OAT\Info(
    version: "1.0.0",
    title: "Oryon Gestao API - Documentacao de Endpoints",
    description: "API RESTful profissional desenvolvida em Laravel 11 para gestao de departamentos, colaboradores e movimentacao estrategica de equipes.",
    contact: new OAT\Contact(name: "Jorge Carvalho", email: "carvalhojorge7@gmail.com")
)]
#[OAT\Server(
    url: "http://localhost:8000",
    description: "Servidor Local de Desenvolvimento"
)]
#[OAT\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Insira o token JWT retornado no endpoint /api/auth/login para autenticar as requisicoes."
)]
class OpenApiSpec
{
}
