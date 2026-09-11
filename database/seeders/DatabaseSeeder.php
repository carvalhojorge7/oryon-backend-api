<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    ### Popula o banco com usuario administrador e dados de teste estrategicos ###
    public function run(): void
    {
        ### 1. Usuario Administrador para Login nos Testes e Swagger ###
        User::updateOrCreate(
            ['email' => 'admin@oryon.com.br'],
            [
                'name' => 'Jorge Carvalho Admin',
                'password' => Hash::make('senha123'),
            ]
        );

        ### 2. Departamento Tecnologia da Informacao ###
        $deptoTi = Department::create([
            'name' => 'Tecnologia da Informacao',
            'description' => 'Setor responsavel por desenvolvimento de software, infraestrutura e inovacao.',
            'status' => 'ativo',
        ]);

        // 5 Colaboradores ativos em TI
        $colaboradoresTiAtivos = [
            ['name' => 'Carlos Eduardo Lima', 'email' => 'carlos.lima@oryon.com.br', 'role' => 'Desenvolvedor Backend Senior', 'hired_at' => '2022-03-15'],
            ['name' => 'Beatriz Mendes Rocha', 'email' => 'beatriz.rocha@oryon.com.br', 'role' => 'Tech Lead', 'hired_at' => '2021-08-01'],
            ['name' => 'Lucas Ferreira Costa', 'email' => 'lucas.costa@oryon.com.br', 'role' => 'DevOps Engineer', 'hired_at' => '2023-01-10'],
            ['name' => 'Juliana Martins Prado', 'email' => 'juliana.prado@oryon.com.br', 'role' => 'Desenvolvedora Full-stack Pleno', 'hired_at' => '2023-06-20'],
            ['name' => 'Rafael Souza Guimaraes', 'email' => 'rafael.guimaraes@oryon.com.br', 'role' => 'Engenheiro de Dados', 'hired_at' => '2024-02-01'],
        ];

        foreach ($colaboradoresTiAtivos as $colab) {
            Employee::create(array_merge($colab, [
                'department_id' => $deptoTi->id,
                'status' => 'ativo',
            ]));
        }

        // 2 Colaboradores inativos em TI
        $colaboradoresTiInativos = [
            ['name' => 'Fernando Henrique Alencar', 'email' => 'fernando.alencar@oryon.com.br', 'role' => 'Desenvolvedor Junior', 'hired_at' => '2022-01-10'],
            ['name' => 'Mariana Silveira Dias', 'email' => 'mariana.dias@oryon.com.br', 'role' => 'Analista de QA', 'hired_at' => '2022-05-15'],
        ];

        foreach ($colaboradoresTiInativos as $colab) {
            Employee::create(array_merge($colab, [
                'department_id' => $deptoTi->id,
                'status' => 'inativo',
            ]));
        }

        ### 3. Departamento Recursos Humanos ###
        $deptoRh = Department::create([
            'name' => 'Recursos Humanos',
            'description' => 'Gestao de pessoas, recrutamento, cultura organizacional e beneficios.',
            'status' => 'ativo',
        ]);

        $colaboradoresRh = [
            ['name' => 'Patricia Helena Vasconcelos', 'email' => 'patricia.vasconcelos@oryon.com.br', 'role' => 'Gerente de RH', 'hired_at' => '2020-11-01'],
            ['name' => 'Tiago Fonseca Ramos', 'email' => 'tiago.ramos@oryon.com.br', 'role' => 'Business Partner', 'hired_at' => '2023-04-12'],
            ['name' => 'Aline Cristina Borges', 'email' => 'aline.borges@oryon.com.br', 'role' => 'Analista de Recrutamento', 'hired_at' => '2023-09-01'],
        ];

        foreach ($colaboradoresRh as $colab) {
            Employee::create(array_merge($colab, [
                'department_id' => $deptoRh->id,
                'status' => 'ativo',
            ]));
        }

        ### 4. Departamento Projetos Especiais (Vazio - pronto para testes de exclusao e destino de transferencia) ###
        Department::create([
            'name' => 'Projetos Especiais',
            'description' => 'Departamento estrategico reservado para novas iniciativas e squads temporarias.',
            'status' => 'ativo',
        ]);

        ### 5. Departamento Arquivo Morto (Inativo) ###
        Department::create([
            'name' => 'Arquivo Morto',
            'description' => 'Departamento desativado para historico.',
            'status' => 'inativo',
        ]);
    }
}
