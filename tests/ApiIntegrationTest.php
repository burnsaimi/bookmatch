<?php
use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase
{
    private string $baseUrl = 'http://127.0.0.1:8000/BookMatch/api';

    private function postJson(string $endpoint, array $data): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        $payload = json_encode($data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body'   => (string) $response,
            'json'   => json_decode((string) $response, true) ?? []
        ];
    }

    public function testFluxoCadastroELoginPositivo(): void
    {
        $email = 'leitor_' . uniqid() . '@ufsm.br';
        $senha = 'senha123';

        $resCadastro = $this->postJson('/cadastro.php', [
            'nome'    => 'Usuario Teste QAOps',
            'email'   => $email,
            'senha'   => $senha,
            'cidade'  => 'Frederico Westphalen',
            'uf'      => 'RS',
            'generos' => ['Ficção Científica', 'Fantasia']
        ]);

        $this->assertEquals(200, $resCadastro['status'], 'Falha no cadastro: ' . $resCadastro['body']);
        $this->assertTrue($resCadastro['json']['sucesso'] ?? false);

        $resLogin = $this->postJson('/login.php', [
            'email' => $email,
            'senha' => $senha
        ]);

        $this->assertEquals(200, $resLogin['status'], 'Falha no login: ' . $resLogin['body']);
    }

    public function testCadastroComEmailDuplicadoRetornaConflito409(): void
    {
        $email = 'duplicado_' . uniqid() . '@ufsm.br';
        $dados = [
            'nome'   => 'Usuario Duplicado',
            'email'  => $email,
            'senha'  => 'senha123',
            'cidade' => 'Frederico Westphalen',
            'uf'     => 'RS'
        ];

        $primeiro = $this->postJson('/cadastro.php', $dados);
        $this->assertEquals(200, $primeiro['status']);

        $segundo = $this->postJson('/cadastro.php', $dados);
        $this->assertEquals(409, $segundo['status'], 'Deveria bloquear e-mail duplicado com HTTP 409');
    }

    public function testCadastroComEmailInvalidoRetornaErro400(): void
    {
        $res = $this->postJson('/cadastro.php', [
            'nome'  => 'Usuario Invalido',
            'email' => 'email-sem-arroba',
            'senha' => 'senha123'
        ]);

        $this->assertEquals(400, $res['status']);
    }

    public function testLoginComCredenciaisInvalidasCasoNegativo(): void
    {
        $res = $this->postJson('/login.php', [
            'email' => 'naoexiste@ufsm.br',
            'senha' => 'senhaerrada'
        ]);

        $this->assertNotEquals(200, $res['status']);
        $this->assertNotEquals(500, $res['status']);
    }
}