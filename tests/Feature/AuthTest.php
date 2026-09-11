<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    ### Autenticacao com credenciais validas ###
    public function test_usuario_consegue_fazer_login_com_credenciais_validas(): void
    {
        $usuario = User::factory()->create([
            'email' => 'admin@oryon.com.br',
            'password' => bcrypt('senha123'),
        ]);

        $resposta = $this->postJson('/api/auth/login', [
            'email' => 'admin@oryon.com.br',
            'password' => 'senha123',
        ]);

        $resposta->assertStatus(200)
            ->assertJsonStructure([
                'token_de_acesso',
                'tipo_token',
                'expira_em_segundos',
                'usuario' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);
    }

    ### Falha de login com senha incorreta ###
    public function test_falha_ao_tentar_login_com_senha_incorreta(): void
    {
        User::factory()->create([
            'email' => 'admin@oryon.com.br',
            'password' => bcrypt('senha123'),
        ]);

        $resposta = $this->postJson('/api/auth/login', [
            'email' => 'admin@oryon.com.br',
            'password' => 'senha_errada',
        ]);

        $resposta->assertStatus(401)
            ->assertJson([
                'mensagem' => 'Credenciais invalidas. Verifique seu e-mail e senha.',
            ]);
    }

    ### Bloqueio de acesso a rota protegida sem token ###
    public function test_bloqueia_acesso_a_rotas_protegidas_sem_token(): void
    {
        $resposta = $this->getJson('/api/auth/me');

        $resposta->assertStatus(401)
            ->assertJson([
                'mensagem' => 'Acesso nao autorizado. Forneca um token JWT valido.',
            ]);
    }

    ### Consulta de dados do usuario autenticado ###
    public function test_retorna_dados_do_usuario_autenticado_com_token(): void
    {
        $usuario = User::factory()->create();
        $token = auth('api')->login($usuario);

        $resposta = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $resposta->assertStatus(200)
            ->assertJsonPath('usuario.email', $usuario->email);
    }

    ### Encerramento de sessao via logout ###
    public function test_encerra_sessao_e_invalida_token(): void
    {
        $usuario = User::factory()->create();
        $token = auth('api')->login($usuario);

        $resposta = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $resposta->assertStatus(200)
            ->assertJson([
                'mensagem' => 'Sessao encerrada com sucesso.',
            ]);
    }
}
