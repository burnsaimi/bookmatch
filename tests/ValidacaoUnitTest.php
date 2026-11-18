<?php
use PHPUnit\Framework\TestCase;

class ValidacaoUnitTest extends TestCase
{
    private function validarDadosCadastro(string $email, string $senha): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($senha) >= 6;
    }

    public function testCadastroValidoDeveRetornarTrue(): void
    {
        $this->assertTrue($this->validarDadosCadastro('leitor@ufsm.br', 'senha123'));
    }

    public function testCadastroComSenhaCurtaOuEmailInvalidoDeveRetornarFalse(): void
    {
        $this->assertFalse($this->validarDadosCadastro('email-invalido', '123'));
    }
}