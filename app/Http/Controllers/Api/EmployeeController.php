<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OAT;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    #[OAT\Get(
        path: "/api/employees",
        summary: "Listar colaboradores",
        description: "Retorna listagem paginada de colaboradores com suporte a filtros por departamento e status.",
        tags: ["Colaboradores"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "department_id", in: "query", description: "Filtro por departamento", required: false, schema: new OAT\Schema(type: "integer")),
            new OAT\Parameter(name: "status", in: "query", description: "Filtro por status (ativo ou inativo)", required: false, schema: new OAT\Schema(type: "string", enum: ["ativo", "inativo"])),
            new OAT\Parameter(name: "per_page", in: "query", description: "Registros por pagina", required: false, schema: new OAT\Schema(type: "integer", default: 15)),
            new OAT\Parameter(name: "page", in: "query", description: "Numero da pagina", required: false, schema: new OAT\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: "Listagem de colaboradores",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "data", type: "array", items: new OAT\Items(type: "object")),
                        new OAT\Property(property: "links", type: "object"),
                        new OAT\Property(property: "meta", type: "object")
                    ]
                )
            ),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['department_id', 'status']);
        $porPagina = (int) $request->input('per_page', 15);

        $colaboradores = $this->employeeService->listar($filtros, $porPagina);

        return EmployeeResource::collection($colaboradores);
    }

    #[OAT\Post(
        path: "/api/employees",
        summary: "Cadastrar colaborador",
        description: "Cadastra um novo colaborador no sistema.",
        tags: ["Colaboradores"],
        security: [["bearerAuth" => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ["name", "email", "department_id", "role", "hired_at"],
                properties: [
                    new OAT\Property(property: "name", type: "string", example: "Carlos Eduardo Lima"),
                    new OAT\Property(property: "email", type: "string", format: "email", example: "carlos.lima@oryon.com.br"),
                    new OAT\Property(property: "department_id", type: "integer", example: 1),
                    new OAT\Property(property: "role", type: "string", example: "Desenvolvedor Backend Senior"),
                    new OAT\Property(property: "hired_at", type: "string", format: "date", example: "2024-01-15"),
                    new OAT\Property(property: "status", type: "string", enum: ["ativo", "inativo"], example: "ativo")
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: "Colaborador cadastrado com sucesso",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OAT\Response(response: 422, description: "Erro de validacao"),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $colaborador = $this->employeeService->criar($request->validated());

        return (new EmployeeResource($colaborador))
            ->response()
            ->setStatusCode(201);
    }

    #[OAT\Get(
        path: "/api/employees/{id}",
        summary: "Exibir colaborador",
        description: "Retorna os detalhes de um colaborador especifico.",
        tags: ["Colaboradores"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do colaborador", schema: new OAT\Schema(type: "integer"))
        ],
        responses: [
            new OAT\Response(response: 200, description: "Dados do colaborador"),
            new OAT\Response(response: 404, description: "Colaborador nao encontrado"),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function show(int $id): EmployeeResource
    {
        $colaborador = $this->employeeService->buscarPorId($id);

        return new EmployeeResource($colaborador);
    }

    #[OAT\Put(
        path: "/api/employees/{id}",
        summary: "Atualizar colaborador",
        description: "Atualiza os dados cadastrais de um colaborador.",
        tags: ["Colaboradores"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do colaborador", schema: new OAT\Schema(type: "integer"))
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: "name", type: "string", example: "Carlos Eduardo Lima"),
                    new OAT\Property(property: "role", type: "string", example: "Tech Lead"),
                    new OAT\Property(property: "status", type: "string", enum: ["ativo", "inativo"], example: "ativo")
                ]
            )
        ),
        responses: [
            new OAT\Response(response: 200, description: "Colaborador atualizado com sucesso"),
            new OAT\Response(response: 404, description: "Colaborador nao encontrado"),
            new OAT\Response(response: 422, description: "Erro de validacao")
        ]
    )]
    public function update(UpdateEmployeeRequest $request, int $id): EmployeeResource
    {
        $colaborador = $this->employeeService->atualizar($id, $request->validated());

        return new EmployeeResource($colaborador);
    }

    #[OAT\Delete(
        path: "/api/employees/{id}",
        summary: "Inativar colaborador",
        description: "Inativa o colaborador alterando seu status para inativo e aplicando Soft Delete.",
        tags: ["Colaboradores"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do colaborador", schema: new OAT\Schema(type: "integer"))
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: "Colaborador inativado com sucesso",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "mensagem", type: "string", example: "Colaborador inativado com sucesso.")
                    ]
                )
            ),
            new OAT\Response(response: 404, description: "Colaborador nao encontrado")
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->employeeService->inativar($id);

        return response()->json([
            'mensagem' => 'Colaborador inativado com sucesso.',
        ], 200);
    }
}
