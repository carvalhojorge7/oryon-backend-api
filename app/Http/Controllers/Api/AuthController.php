<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OAT;

class AuthController extends Controller
{
    #[OAT\Post(
        path: "/api/auth/login",
        summary: "Autenticacao do usuario",
        description: "Valida as credenciais informadas e retorna um token de acesso JWT.",
        tags: ["Autenticacao"],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OAT\Property(property: "email", type: "string", format: "email", example: "admin@oryon.com.br"),
                    new OAT\Property(property: "password", type: "string", format: "password", example: "senha123")
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: "Login realizado com sucesso",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "token_de_acesso", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOi..."),
                        new OAT\Property(property: "tipo_token", type: "string", example: "bearer"),
                        new OAT\Property(property: "expira_em_segundos", type: "integer", example: 3600),
                        new OAT\Property(property: "usuario", type: "object")
                    ]
                )
            ),
            new OAT\Response(response: 401, description: "Credenciais invalidas")
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credenciais = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credenciais)) {
            return response()->json([
                'mensagem' => 'Credenciais invalidas. Verifique seu e-mail e senha.',
            ], 401);
        }

        return $this->retornarTokenEstruturado($token);
    }

    #[OAT\Get(
        path: "/api/auth/me",
        summary: "Dados do usuario autenticado",
        description: "Retorna as informacoes cadastrais do usuario autenticado.",
        tags: ["Autenticacao"],
        security: [["bearerAuth" => []]],
        responses: [
            new OAT\Response(
                response: 200,
                description: "Dados do usuario retornados com sucesso",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "usuario", type: "object")
                    ]
                )
            ),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function me(): JsonResponse
    {
        return response()->json([
            'usuario' => auth('api')->user(),
        ]);
    }

    #[OAT\Post(
        path: "/api/auth/logout",
        summary: "Encerramento de sessao",
        description: "Invalida o token JWT atual.",
        tags: ["Autenticacao"],
        security: [["bearerAuth" => []]],
        responses: [
            new OAT\Response(
                response: 200,
                description: "Sessao encerrada",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "mensagem", type: "string", example: "Sessao encerrada com sucesso.")
                    ]
                )
            ),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'mensagem' => 'Sessao encerrada com sucesso.',
        ]);
    }

    #[OAT\Post(
        path: "/api/auth/refresh",
        summary: "Renovacao de token",
        description: "Renova o token JWT de acesso expirado gerando um novo tempo de validade.",
        tags: ["Autenticacao"],
        security: [["bearerAuth" => []]],
        responses: [
            new OAT\Response(
                response: 200,
                description: "Token renovado com sucesso"
            ),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function refresh(): JsonResponse
    {
        return $this->retornarTokenEstruturado(auth('api')->refresh());
    }

    private function retornarTokenEstruturado(string $token): JsonResponse
    {
        return response()->json([
            'token_de_acesso' => $token,
            'tipo_token' => 'bearer',
            'expira_em_segundos' => auth('api')->factory()->getTTL() * 60,
            'usuario' => auth('api')->user(),
        ]);
    }
}
