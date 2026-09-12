<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Oryon Gestao API - Documentacao de Endpoints",
 *     version="1.0.0",
 *     description="API RESTful profissional desenvolvida em Laravel 11 para gestao de departamentos, colaboradores e movimentacao estrategica de equipes.",
 *     @OA\Contact(
 *         name="Jorge Carvalho",
 *         email="carvalhojorge7@gmail.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor Local de Desenvolvimento"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Insira o token JWT retornado no endpoint /api/auth/login para autenticar as requisicoes."
 * )
 */
abstract class Controller
{
    //
}
