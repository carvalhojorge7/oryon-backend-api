<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\TransferEmployeesRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OAT;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService,
        protected TransferService $transferService
    ) {}

    #[OAT\Get(
        path: "/api/departments",
        summary: "Listar departamentos",
        description: "Retorna listagem paginada de departamentos com suporte a filtro por status e cache inteligente.",
        tags: ["Departamentos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "status", in: "query", description: "Filtro pelo status do departamento (ativo ou inativo)", required: false, schema: new OAT\Schema(type: "string", enum: ["ativo", "inativo"])),
            new OAT\Parameter(name: "per_page", in: "query", description: "Quantidade de registros por pagina", required: false, schema: new OAT\Schema(type: "integer", default: 15)),
            new OAT\Parameter(name: "page", in: "query", description: "Numero da pagina", required: false, schema: new OAT\Schema(type: "integer", default: 1))
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: "Listagem de departamentos",
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
        $filtros = $request->only(['status']);
        $porPagina = (int) $request->input('per_page', 15);

        $departamentos = $this->departmentService->listar($filtros, $porPagina);

        return DepartmentResource::collection($departamentos);
    }

    #[OAT\Post(
        path: "/api/departments",
        summary: "Criar departamento",
        description: "Cadastra um novo departamento no sistema.",
        tags: ["Departamentos"],
        security: [["bearerAuth" => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ["name"],
                properties: [
                    new OAT\Property(property: "name", type: "string", example: "Engenharia de Software"),
                    new OAT\Property(property: "description", type: "string", example: "Equipe responsavel pelo desenvolvimento de software."),
                    new OAT\Property(property: "status", type: "string", enum: ["ativo", "inativo"], example: "ativo")
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: "Departamento criado com sucesso",
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
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $departamento = $this->departmentService->criar($request->validated());

        return (new DepartmentResource($departamento))
            ->response()
            ->setStatusCode(201);
    }

    #[OAT\Get(
        path: "/api/departments/{id}",
        summary: "Exibir departamento e colaboradores",
        description: "Retorna os dados de um departamento juntamente com seus colaboradores associados.",
        tags: ["Departamentos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do departamento", schema: new OAT\Schema(type: "integer"))
        ],
        responses: [
            new OAT\Response(response: 200, description: "Dados do departamento"),
            new OAT\Response(response: 404, description: "Departamento nao encontrado"),
            new OAT\Response(response: 401, description: "Nao autenticado")
        ]
    )]
    public function show(int $id): DepartmentResource
    {
        $departamento = $this->departmentService->buscarPorId($id);

        return new DepartmentResource($departamento);
    }

    #[OAT\Put(
        path: "/api/departments/{id}",
        summary: "Atualizar departamento",
        description: "Atualiza os dados de um departamento existente.",
        tags: ["Departamentos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do departamento", schema: new OAT\Schema(type: "integer"))
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: "name", type: "string", example: "Tecnologia e Inovacao"),
                    new OAT\Property(property: "description", type: "string", example: "Descricao atualizada."),
                    new OAT\Property(property: "status", type: "string", enum: ["ativo", "inativo"], example: "ativo")
                ]
            )
        ),
        responses: [
            new OAT\Response(response: 200, description: "Departamento atualizado com sucesso"),
            new OAT\Response(response: 404, description: "Departamento nao encontrado"),
            new OAT\Response(response: 422, description: "Erro de validacao")
        ]
    )]
    public function update(UpdateDepartmentRequest $request, int $id): DepartmentResource
    {
        $departamento = $this->departmentService->atualizar($id, $request->validated());

        return new DepartmentResource($departamento);
    }

    #[OAT\Delete(
        path: "/api/departments/{id}",
        summary: "Remover departamento",
        description: "Exclui um departamento sem colaboradores ativos vinculados.",
        tags: ["Departamentos"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do departamento", schema: new OAT\Schema(type: "integer"))
        ],
        responses: [
            new OAT\Response(response: 204, description: "Departamento excluido com sucesso"),
            new OAT\Response(
                response: 422,
                description: "Bloqueio por colaboradores ativos vinculados",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "mensagem", type: "string", example: "O departamento possui colaboradores ativos e nao pode ser excluido.")
                    ]
                )
            ),
            new OAT\Response(response: 404, description: "Departamento nao encontrado")
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->departmentService->excluir($id);

        return response()->json(null, 204);
    }

    #[OAT\Post(
        path: "/api/departments/{id}/transfer-employees",
        summary: "Transferir colaboradores",
        description: "Transfere todos os colaboradores ativos do departamento de origem para o departamento de destino com execucao atomica e rollback em caso de falha.",
        tags: ["Transferencia"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OAT\Parameter(name: "id", in: "path", required: true, description: "ID do departamento de origem", schema: new OAT\Schema(type: "integer"))
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ["target_department_id"],
                properties: [
                    new OAT\Property(property: "target_department_id", type: "integer", example: 2)
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: "Transferencia concluida com sucesso",
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: "message", type: "string", example: "Colaboradores transferidos com sucesso."),
                        new OAT\Property(property: "transferred", type: "integer", example: 5)
                    ]
                )
            ),
            new OAT\Response(response: 422, description: "Origem e destino iguais ou erro de validacao"),
            new OAT\Response(response: 404, description: "Departamento nao encontrado")
        ]
    )]
    public function transferEmployees(TransferEmployeesRequest $request, int $id): JsonResponse
    {
        $destinoId = (int) $request->input('target_department_id');

        $quantidadeTransferida = $this->transferService->transferir($id, $destinoId);

        return response()->json([
            'message' => 'Colaboradores transferidos com sucesso.',
            'transferred' => $quantidadeTransferida,
        ], 200);
    }
}
