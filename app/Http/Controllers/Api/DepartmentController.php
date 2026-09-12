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

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departmentService,
        protected TransferService $transferService
    ) {}

    ### Lista departamentos com paginacao e filtro de status ###
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['status']);
        $porPagina = (int) $request->input('per_page', 15);

        $departamentos = $this->departmentService->listar($filtros, $porPagina);

        return DepartmentResource::collection($departamentos);
    }

    ### Cria novo departamento ###
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $departamento = $this->departmentService->criar($request->validated());

        return (new DepartmentResource($departamento))
            ->response()
            ->setStatusCode(201);
    }

    ### Exibe departamento e colaboradores vinculados ###
    public function show(int $id): DepartmentResource
    {
        $departamento = $this->departmentService->buscarPorId($id);

        return new DepartmentResource($departamento);
    }

    ### Atualiza dados de um departamento ###
    public function update(UpdateDepartmentRequest $request, int $id): DepartmentResource
    {
        $departamento = $this->departmentService->atualizar($id, $request->validated());

        return new DepartmentResource($departamento);
    }

    ### Remove departamento (com validacao de colaboradores ativos) ###
    public function destroy(int $id): JsonResponse
    {
        $this->departmentService->excluir($id);

        return response()->json(null, 204);
    }

    ### Transfere colaboradores ativos para departamento de destino ###
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
