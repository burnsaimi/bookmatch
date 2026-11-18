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

    $stmt = $pdo->prepare('
        SELECT
            m.codmacth,
            m.datamatch,
            CASE
                WHEN c1.usuario_codusuario = :uid THEN c2.usuario_codusuario
                ELSE c1.usuario_codusuario
            END AS outro_usuario_id,
            CASE
                WHEN c1.usuario_codusuario = :uid THEN u2.nome
                ELSE u1.nome
            END AS outro_usuario_nome,
            CASE
                WHEN c1.usuario_codusuario = :uid THEN l2.nome
                ELSE l1.nome
            END AS livro_titulo,
            CASE
                WHEN c1.usuario_codusuario = :uid THEN l2.foto_api
                ELSE l1.foto_api
            END AS livro_foto,
            CASE
                WHEN c1.usuario_codusuario = :uid THEN l2.genero
                ELSE l1.genero
            END AS livro_genero
        FROM matches m
        JOIN curtidas c1 ON m.curtidas_codcurtida  = c1.codcurtida
        JOIN curtidas c2 ON m.curtidas_codcurtida1 = c2.codcurtida
        JOIN usuario  u1 ON c1.usuario_codusuario  = u1.codusuario
        JOIN usuario  u2 ON c2.usuario_codusuario  = u2.codusuario
        JOIN estante  e1 ON c1.estante_cod         = e1.cod
        JOIN estante  e2 ON c2.estante_cod         = e2.cod
        JOIN livro    l1 ON e1.livro_codlivro       = l1.codlivro
        JOIN livro    l2 ON e2.livro_codlivro       = l2.codlivro
        WHERE c1.usuario_codusuario = :uid OR c2.usuario_codusuario = :uid
        ORDER BY m.datamatch DESC
    ');
    $stmt->execute([':uid' => $usuarioId]);
    $matches = $stmt->fetchAll();

    echo json_encode(['matches' => $matches]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
