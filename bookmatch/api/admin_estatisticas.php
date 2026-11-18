<?php
require_once 'config.php';

try {
    $pdo = conectar();

    // ── Cards principais ──
    $totalUsuarios = (int)$pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn();
    $totalTrocas   = (int)$pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn();
    $totalPausadas = (int)$pdo->query("SELECT COUNT(*) FROM usuario WHERE ativo = 'Pausado'")->fetchColumn();
    $totalLivros   = (int)$pdo->query('SELECT COUNT(*) FROM livro')->fetchColumn();

    // ── Usuários por perfil ──
    $admins = (int)$pdo->query("SELECT COUNT(*) FROM usuario WHERE tipo_usuario = 'ADMIN'")->fetchColumn();
    $comuns = $totalUsuarios - $admins;
    $pctAdmins = $totalUsuarios > 0 ? round($admins / $totalUsuarios * 100) : 0;
    $pctComuns = $totalUsuarios > 0 ? 100 - $pctAdmins : 0;

    // ── Top estados (por uf) ──
    $stmt = $pdo->query("
        SELECT UPPER(uf) AS uf, COUNT(*) AS total
        FROM usuario
        WHERE uf IS NOT NULL AND uf <> ''
        GROUP BY UPPER(uf)
        ORDER BY total DESC
        LIMIT 5
    ");
    $estados = $stmt->fetchAll();
    $somaEstados = array_sum(array_map(fn($e) => (int)$e['total'], $estados));
    $topEstados = array_map(function ($e) use ($somaEstados) {
        return [
            'uf'  => $e['uf'],
            'total' => (int)$e['total'],
            'pct' => $somaEstados > 0 ? round((int)$e['total'] / $somaEstados * 100) : 0,
        ];
    }, $estados);

    echo json_encode([
        'cards' => [
            'usuarios' => $totalUsuarios,
            'trocas'   => $totalTrocas,
            'pausadas' => $totalPausadas,
            'livros'   => $totalLivros,
        ],
        'perfil' => [
            'admins'    => $admins,
            'comuns'    => $comuns,
            'pct_admins'=> $pctAdmins,
            'pct_comuns'=> $pctComuns,
        ],
        'top_estados' => $topEstados,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
