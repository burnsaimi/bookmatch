# BookMatch

Plataforma web de descoberta e troca de livros. Projeto da disciplina de Qualidade de Software (UFSM) com pipeline DevOps/QAOps no GitHub Actions.

## Tecnologias

- Frontend: HTML5, CSS3 e JavaScript (Fetch API)
- Backend: API REST em PHP 8.2 (`BookMatch/api/*.php`)
- Banco de dados: MySQL 8.0 (`BookMatch/bookmatch.sql`)
- Testes e análise: PHPUnit 10.5 e PHPStan, instalados via Composer

## Como executar localmente

1. Instale as dependências: `composer install`
2. Crie o banco e importe o esquema: `mysql -u root -p bookmatch < BookMatch/bookmatch.sql`
3. Defina as variáveis de ambiente (nenhuma credencial fica no repositório):
   `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS`
4. Inicie o servidor na raiz do repositório: `php -S 127.0.0.1:8000` e acesse `http://127.0.0.1:8000/BookMatch/` no navegador.

## Como executar os testes

Com o servidor e o banco ativos:

```
php vendor/bin/phpunit tests --log-junit test-report.xml
```

## Verificações de qualidade (pipeline)

O workflow `.github/workflows/qaops.yml` roda em todo push ou pull request na `main`:

| Gate | Verificação | Comando |
|---|---|---|
| 1 | Sintaxe PHP | `find BookMatch -name "*.php" -print0 \| xargs -0 -n1 php -l` |
| 2 | Análise estática | `php vendor/bin/phpstan analyse BookMatch/api --level=1` |
| 3 | Testes automatizados | `php vendor/bin/phpunit tests --log-junit test-report.xml` |

Se qualquer gate falhar, o pipeline é bloqueado. O relatório `test-report.xml` é publicado como artefato (`relatorio-testes-qaops`) mesmo em caso de falha.

## Autores

- Maicon Junior Aimi
- Jeniffer Priscilla Da Silva Freitas
