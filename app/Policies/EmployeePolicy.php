<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    ### Permite visualizar a listagem de colaboradores ###
    public function viewAny(User $user): bool
    {
        return true;
    }

    ### Permite visualizar um colaborador especifico ###
    public function view(User $user, Employee $employee): bool
    {
        return true;
    }

    ### Permite cadastrar colaborador ###
    public function create(User $user): bool
    {
        return true;
    }

    ### Permite atualizar colaborador ###
    public function update(User $user, Employee $employee): bool
    {
        return true;
    }

    ### Permite inativar colaborador ###
    public function delete(User $user, Employee $employee): bool
    {
        return true;
    }
}
