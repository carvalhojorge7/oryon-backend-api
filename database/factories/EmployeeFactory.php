<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $cargos = [
            'Desenvolvedor Backend Pleno',
            'Desenvolvedor Backend Senior',
            'Engenheiro de Software',
            'Analista de Sistemas',
            'Coordenador de TI',
            'Analista de RH Senior',
            'Especialista em Seguranca',
            'Tech Lead',
            'DevOps Engineer',
            'Product Owner',
        ];

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'department_id' => Department::factory(),
            'role' => fake()->randomElement($cargos),
            'hired_at' => fake()->dateTimeBetween('-4 years', '-1 month')->format('Y-m-d'),
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
