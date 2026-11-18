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

    $totalLivros = $pdo->prepare('SELECT COUNT(*) AS total FROM estante WHERE usuario_codusuario = ?');
    $totalLivros->execute([$usuarioId]);
    $livros = $totalLivros->fetch()['total'];

    $totalMatches = $pdo->prepare(
        'SELECT COUNT(*) AS total FROM matches m
         JOIN curtidas c1 ON m.curtidas_codcurtida  = c1.codcurtida
         JOIN curtidas c2 ON m.curtidas_codcurtida1 = c2.codcurtida
         WHERE c1.usuario_codusuario = ? OR c2.usuario_codusuario = ?'
    );
    $totalMatches->execute([$usuarioId, $usuarioId]);
    $matches = $totalMatches->fetch()['total'];

    $genFav = $pdo->prepare(
        'SELECT g.genero FROM generos_favo gf
         JOIN generos g ON gf.generos_codgen = g.codgen
         WHERE gf.usuario_codusuario = ?
         LIMIT 1'
    );
    $genFav->execute([$usuarioId]);
    $genero = $genFav->fetch()['genero'] ?? '—';

    echo json_encode([
        'total_livros'    => (int)$livros,
        'matches'         => (int)$matches,
        'genero_favorito' => $genero,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
