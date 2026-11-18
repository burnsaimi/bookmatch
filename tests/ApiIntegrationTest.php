<?php
use PHPUnit\Framework\TestCase;

class ApiIntegrationTest extends TestCase
{
    private string $baseUrl = 'http://127.0.0.1:8000/BookMatch/api';

    private function postRequest(string $endpoint, array $data): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $httpCode, 'body' => (string) $response];
    }

    public function testFluxoCadastroELoginPositivo(): void
    {
        $email = 'teste_' . uniqid() . '@ufsm.br';
        $resCadastro = $this->postRequest('/cadastro.php', [
            'nome' => 'Usuário Teste',
            'email' => $email,
            'senha' => 'senha123'
        ]);
        $this->assertEquals(200, $resCadastro['status']);

        $resLogin = $this->postRequest('/login.php', [
            'email' => $email,
            'senha' => 'senha123'
        ]);
        $this->assertEquals(200, $resLogin['status']);
    }

    public function testLoginComCredenciaisInvalidasCasoNegativo(): void
    {
        $res = $this->postRequest('/login.php', [
            'email' => 'naoexiste@ufsm.br',
            'senha' => 'senhaerrada'
        ]);
        // Garante que a API trata o erro sem gerar falha fatal (HTTP 500)
        $this->assertNotEquals(500, $res['status']);
    }
}