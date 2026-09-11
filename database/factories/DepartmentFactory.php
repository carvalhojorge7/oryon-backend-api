<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departamentos = [
            'Tecnologia da Informacao',
            'Recursos Humanos',
            'Engenharia de Software',
            'Operacoes e Logistica',
            'Financeiro e Controladoria',
            'Marketing e Vendas',
            'Atendimento e Suporte',
            'Qualidade e Processos',
            'Seguranca da Informacao',
            'Projetos e Inovacao',
        ];

        return [
            'name' => fake()->unique()->randomElement($departamentos) . ' ' . fake()->numerify('##'),
            'description' => fake()->paragraph(2),
            'status' => fake()->randomElement(['ativo', 'ativo', 'ativo', 'inativo']),
        ];
    }

    public function ativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ativo',
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inativo',
        ]);
    }
}
