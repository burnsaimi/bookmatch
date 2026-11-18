<?php
require_once 'config.php';

$usuarioId = (int)($_GET['usuario'] ?? 0);
if (!$usuarioId) {
    http_response_code(400);
    echo json_encode(['erro' => 'Usuário não informado.']);
    exit;
}

try {
    $pdo = conectar();

    // Livros de outros usuários que o atual ainda não curtiu
    $stmt = $pdo->prepare('
        SELECT
            e.cod        AS estante_cod,
            l.codlivro,
            l.nome       AS titulo,
            l.foto_api AS foto,
            l.genero,
            u.codusuario AS dono_id,
            u.nome       AS dono_nome
        FROM estante e
        JOIN livro    l ON e.livro_codlivro   = l.codlivro
        JOIN usuario  u ON e.usuario_codusuario = u.codusuario
        WHERE e.usuario_codusuario != ?
          AND e.cod NOT IN (
              SELECT estante_cod FROM curtidas WHERE usuario_codusuario = ?
          )
        ORDER BY RAND()
        LIMIT 20
    ');
    $stmt->execute([$usuarioId, $usuarioId]);
    $livros = $stmt->fetchAll();

    echo json_encode(['livros' => $livros]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
