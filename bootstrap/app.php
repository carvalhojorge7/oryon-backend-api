<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        ### Resposta humanizada para falhas de autenticacao ###
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'mensagem' => 'Acesso nao autorizado. Forneca um token JWT valido.',
                ], 401);
            }
        });

        ### Resposta humanizada para regras de negocio violadas ###
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'mensagem' => $e->getMessage(),
                ], 422);
            }
        });

        ### Resposta humanizada para registros nao encontrados ###
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'mensagem' => 'O recurso solicitado nao foi encontrado.',
                ], 404);
            }
        });

        ### Resposta humanizada para erros de validacao ###
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'mensagem' => 'Os dados fornecidos sao invalidos.',
                    'errors' => $e->errors(),
                    'erros' => $e->errors(),
                ], 422);
            }
        });
    })->create();
