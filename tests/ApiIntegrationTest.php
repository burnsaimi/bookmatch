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

        return ['status' => $httpCode, 'body' => (string) $response];
    }

    public function testFluxoCadastroELoginPositivo(): void
    {
        $email = 'teste_' . uniqid() . '@ufsm.br';
        $resCadastro = $this->postJson('/cadastro.php', [
            'nome' => 'Usuario Teste',
            'email' => $email,
            'senha' => 'senha123',
            'cidade' => 'Frederico Westphalen',
            'estado' => 'RS'
        ]);
        $this->assertEquals(200, $resCadastro['status'], 'Erro no cadastro: ' . $resCadastro['body']);

        $resLogin = $this->postJson('/login.php', [
            'email' => $email,
            'senha' => 'senha123'
        ]);
        $this->assertEquals(200, $resLogin['status'], 'Erro no login: ' . $resLogin['body']);
    }

    public function testLoginComCredenciaisInvalidasCasoNegativo(): void
    {
        $res = $this->postJson('/login.php', [
            'email' => 'naoexiste@ufsm.br',
            'senha' => 'senhaerrada'
        ]);
        $this->assertNotEquals(500, $res['status']);
    }
}