<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    ### Relacionamento com todos os colaboradores ###
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    ### Relacionamento com colaboradores ativos ###
    public function activeEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id')->where('status', 'ativo');
    }

    ### Scopes locais para filtro por status ###
    public function scopeAtivo($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeInativo($query)
    {
        return $query->where('status', 'inativo');
    }
}
