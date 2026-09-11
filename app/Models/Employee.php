<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'name',
        'email',
        'department_id',
        'role',
        'hired_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'hired_at' => 'date',
            'status' => 'string',
        ];
    }

    ### Relacionamento com o departamento vinculado ###
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    ### Scopes locais de consulta ###
    public function scopeAtivo($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeInativo($query)
    {
        return $query->where('status', 'inativo');
    }

    public function scopeDoDepartamento($query, $departamentoId)
    {
        return $query->where('department_id', $departamentoId);
    }
}
