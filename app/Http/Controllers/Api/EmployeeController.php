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

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    ### Lista colaboradores com paginacao e filtros (department_id e status) ###
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['department_id', 'status']);
        $porPagina = (int) $request->input('per_page', 15);

        $colaboradores = $this->employeeService->listar($filtros, $porPagina);

        return EmployeeResource::collection($colaboradores);
    }

    ### Cadastra novo colaborador ###
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $colaborador = $this->employeeService->criar($request->validated());

        return (new EmployeeResource($colaborador))
            ->response()
            ->setStatusCode(201);
    }

    ### Exibe detalhes do colaborador com departamento associado ###
    public function show(int $id): EmployeeResource
    {
        $colaborador = $this->employeeService->buscarPorId($id);

        return new EmployeeResource($colaborador);
    }

    ### Atualiza dados do colaborador ###
    public function update(UpdateEmployeeRequest $request, int $id): EmployeeResource
    {
        $colaborador = $this->employeeService->atualizar($id, $request->validated());

        return new EmployeeResource($colaborador);
    }

    ### Inativa colaborador atraves do endpoint DELETE ###
    public function destroy(int $id): JsonResponse
    {
        $this->employeeService->inativar($id);

        return response()->json([
            'mensagem' => 'Colaborador inativado com sucesso.',
        ], 200);
    }
}
