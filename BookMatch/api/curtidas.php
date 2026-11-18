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

    // ── STATS ──
    $stRecebidas = $pdo->prepare('SELECT COUNT(*) AS total FROM curtidas WHERE usuario_codusuario1 = ?');
    $stRecebidas->execute([$usuarioId]);
    $totalRecebidas = (int)$stRecebidas->fetch()['total'];

    $stDadas = $pdo->prepare('SELECT COUNT(*) AS total FROM curtidas WHERE usuario_codusuario = ?');
    $stDadas->execute([$usuarioId]);
    $totalDadas = (int)$stDadas->fetch()['total'];

    $stMatches = $pdo->prepare('
        SELECT COUNT(*) AS total FROM matches m
        JOIN curtidas c1 ON m.curtidas_codcurtida  = c1.codcurtida
        JOIN curtidas c2 ON m.curtidas_codcurtida1 = c2.codcurtida
        WHERE c1.usuario_codusuario = ? OR c2.usuario_codusuario = ?
    ');
    $stMatches->execute([$usuarioId, $usuarioId]);
    $totalMatches = (int)$stMatches->fetch()['total'];

    // Curtidas dadas ainda sem match correspondente
    $stAguardando = $pdo->prepare('
        SELECT COUNT(*) AS total FROM curtidas c
        WHERE c.usuario_codusuario = ?
          AND c.codcurtida NOT IN (
              SELECT curtidas_codcurtida  FROM matches
              UNION
              SELECT curtidas_codcurtida1 FROM matches
          )
    ');
    $stAguardando->execute([$usuarioId]);
    $totalAguardando = (int)$stAguardando->fetch()['total'];

    // ── CURTIDAS RECEBIDAS ──
    $recStmt = $pdo->prepare('
        SELECT
            c.codcurtida,
            c.datacurtida,
            u.codusuario,
            u.nome,
            (SELECT COUNT(*) FROM estante WHERE usuario_codusuario = u.codusuario) AS total_livros,
            EXISTS (
                SELECT 1 FROM matches m
                WHERE (m.curtidas_codcurtida = c.codcurtida OR m.curtidas_codcurtida1 = c.codcurtida)
            ) AS ja_match
        FROM curtidas c
        JOIN usuario u ON c.usuario_codusuario = u.codusuario
        WHERE c.usuario_codusuario1 = ?
        ORDER BY c.datacurtida DESC
    ');
    $recStmt->execute([$usuarioId]);
    $recebidas = $recStmt->fetchAll();

    // ── CURTIDAS DADAS ──
    $dadStmt = $pdo->prepare('
        SELECT
            c.codcurtida,
            c.datacurtida,
            u.codusuario,
            u.nome,
            (SELECT COUNT(*) FROM estante WHERE usuario_codusuario = u.codusuario) AS total_livros,
            EXISTS (
                SELECT 1 FROM matches m
                WHERE (m.curtidas_codcurtida = c.codcurtida OR m.curtidas_codcurtida1 = c.codcurtida)
            ) AS ja_match
        FROM curtidas c
        JOIN usuario u ON c.usuario_codusuario1 = u.codusuario
        WHERE c.usuario_codusuario = ?
        ORDER BY c.datacurtida DESC
    ');
    $dadStmt->execute([$usuarioId]);
    $dadas = $dadStmt->fetchAll();

    echo json_encode([
        'stats' => [
            'recebidas'  => $totalRecebidas,
            'dadas'      => $totalDadas,
            'matches'    => $totalMatches,
            'aguardando' => $totalAguardando,
        ],
        'recebidas' => $recebidas,
        'dadas'     => $dadas,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
