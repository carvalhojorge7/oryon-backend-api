<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    ### Autentica usuario e gera token JWT ###
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

    ### Retorna dados do usuario autenticado ###
    public function me(): JsonResponse
    {
        return response()->json([
            'usuario' => auth('api')->user(),
        ]);
    }

    ### Invalida a sessao e o token atual ###
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'mensagem' => 'Sessao encerrada com sucesso.',
        ]);
    }

    ### Renova o token de acesso expirado ###
    public function refresh(): JsonResponse
    {
        return $this->retornarTokenEstruturado(auth('api')->refresh());
    }

    ### Estrutura padrao do payload de resposta com token ###
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
