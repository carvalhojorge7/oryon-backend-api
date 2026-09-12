<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    ### Permite visualizar a listagem de departamentos ###
    public function viewAny(User $user): bool
    {
        return true;
    }

    ### Permite visualizar um departamento especifico ###
    public function view(User $user, Department $department): bool
    {
        return true;
    }

    ### Permite criar novo departamento ###
    public function create(User $user): bool
    {
        return true;
    }

    ### Permite atualizar um departamento ###
    public function update(User $user, Department $department): bool
    {
        return true;
    }

    ### Permite excluir um departamento ###
    public function delete(User $user, Department $department): bool
    {
        return true;
    }

    ### Permite transferir colaboradores de um departamento ###
    public function transfer(User $user, Department $department): bool
    {
        return true;
    }
}
