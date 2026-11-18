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

    // ── ESTANTE (livros disponíveis para troca) ──
    $estStmt = $pdo->prepare('
        SELECT
            e.cod AS estante_cod,
            l.codlivro,
            l.nome   AS titulo,
            l.foto_api AS foto,
            l.genero,
            a.nome   AS autor
        FROM estante e
        JOIN livro   l ON e.livro_codlivro = l.codlivro
        LEFT JOIN escreveu es ON es.livro_codlivro = l.codlivro
        LEFT JOIN autores   a ON es.autores_cod_autor = a.cod_autor
        WHERE e.usuario_codusuario = ?
        ORDER BY e.data_adicionado DESC
    ');
    $estStmt->execute([$usuarioId]);
    $estante = $estStmt->fetchAll();

    // ── QUERO LER ──
    $qlStmt = $pdo->prepare('
        SELECT
            ql.cod_quer_ler,
            l.codlivro,
            l.nome   AS titulo,
            l.foto_api AS foto,
            l.genero,
            a.nome   AS autor
        FROM livros_quer_ler ql
        JOIN livro   l ON ql.livro_codlivro = l.codlivro
        LEFT JOIN escreveu es ON es.livro_codlivro = l.codlivro
        LEFT JOIN autores   a ON es.autores_cod_autor = a.cod_autor
        WHERE ql.usuario_codusuario = ?
        ORDER BY ql.cod_quer_ler DESC
    ');
    $qlStmt->execute([$usuarioId]);
    $queroLer = $qlStmt->fetchAll();

    // ── MATCHES (livros dos matches do usuário) ──
    $matchStmt = $pdo->prepare('
        SELECT DISTINCT
            l.codlivro,
            l.nome   AS titulo,
            l.foto_api AS foto,
            l.genero,
            u.nome   AS dono_nome,
            m.datamatch
        FROM matches m
        JOIN curtidas c1 ON m.curtidas_codcurtida  = c1.codcurtida
        JOIN curtidas c2 ON m.curtidas_codcurtida1 = c2.codcurtida
        JOIN estante  e  ON (
            CASE WHEN c1.usuario_codusuario = :uid THEN c2.estante_cod ELSE c1.estante_cod END = e.cod
        )
        JOIN livro    l  ON e.livro_codlivro = l.codlivro
        JOIN usuario  u  ON e.usuario_codusuario = u.codusuario
        WHERE c1.usuario_codusuario = :uid OR c2.usuario_codusuario = :uid
        ORDER BY m.datamatch DESC
    ');
    $matchStmt->execute([':uid' => $usuarioId]);
    $matches = $matchStmt->fetchAll();

    echo json_encode([
        'estante'   => $estante,
        'quero_ler' => $queroLer,
        'matches'   => $matches,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
