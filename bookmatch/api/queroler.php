<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $usuarioId = (int)($_GET['usuario'] ?? 0);
    if (!$usuarioId) {
        http_response_code(400);
        echo json_encode(['erro' => 'Usuário não informado.']);
        exit;
    }

    try {
        $pdo  = conectar();
        $stmt = $pdo->prepare('
            SELECT
                ql.cod_quer_ler,
                l.codlivro,
                l.nome    AS titulo,
                l.foto_api AS foto,
                l.genero,
                a.nome    AS autor
            FROM livros_quer_ler ql
            JOIN livro   l ON ql.livro_codlivro = l.codlivro
            LEFT JOIN escreveu es ON es.livro_codlivro = l.codlivro
            LEFT JOIN autores   a ON es.autores_cod_autor = a.cod_autor
            WHERE ql.usuario_codusuario = ?
            ORDER BY ql.cod_quer_ler DESC
        ');
        $stmt->execute([$usuarioId]);
        echo json_encode(['livros' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

if ($method === 'DELETE') {
    $dados     = json_decode(file_get_contents('php://input'), true);
    $usuarioId = (int)($dados['usuario']  ?? 0);
    $codlivro  = (int)($dados['codlivro'] ?? 0);

    if (!$usuarioId || !$codlivro) {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados incompletos.']);
        exit;
    }

    try {
        $pdo  = conectar();
        $stmt = $pdo->prepare('DELETE FROM livros_quer_ler WHERE usuario_codusuario = ? AND livro_codlivro = ?');
        $stmt->execute([$usuarioId, $codlivro]);
        echo json_encode(['sucesso' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);
