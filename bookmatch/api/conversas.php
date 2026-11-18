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

    // Todos os usuários com quem o atual tem match
    $matchStmt = $pdo->prepare('
        SELECT DISTINCT
            CASE
                WHEN c1.usuario_codusuario = :uid THEN c2.usuario_codusuario
                ELSE c1.usuario_codusuario
            END AS outro_id
        FROM matches m
        JOIN curtidas c1 ON m.curtidas_codcurtida  = c1.codcurtida
        JOIN curtidas c2 ON m.curtidas_codcurtida1 = c2.codcurtida
        WHERE c1.usuario_codusuario = :uid OR c2.usuario_codusuario = :uid
    ');
    $matchStmt->execute([':uid' => $usuarioId]);
    $outrosIds = $matchStmt->fetchAll(PDO::FETCH_COLUMN);

    $conversas = [];
    foreach ($outrosIds as $outroId) {
        $outroId = (int)$outroId;

        $userStmt = $pdo->prepare('SELECT codusuario, nome FROM usuario WHERE codusuario = ?');
        $userStmt->execute([$outroId]);
        $outro = $userStmt->fetch();
        if (!$outro) continue;

        // Última mensagem
        $msgStmt = $pdo->prepare('
            SELECT mensagem, data_envio, usuario_codusuario_remetente
            FROM mensagem
            WHERE (usuario_codusuario_remetente = ? AND usuario_codusuario_destinatario = ?)
               OR (usuario_codusuario_remetente = ? AND usuario_codusuario_destinatario = ?)
            ORDER BY data_envio DESC
            LIMIT 1
        ');
        $msgStmt->execute([$usuarioId, $outroId, $outroId, $usuarioId]);
        $ultima = $msgStmt->fetch();

        // Não lidas (enviadas pelo outro, não respondidas depois)
        $naoLidasStmt = $pdo->prepare('
            SELECT COUNT(*) AS total FROM mensagem
            WHERE usuario_codusuario_remetente = ? AND usuario_codusuario_destinatario = ?
              AND data_envio > COALESCE(
                  (SELECT MAX(data_envio) FROM mensagem
                   WHERE usuario_codusuario_remetente = ? AND usuario_codusuario_destinatario = ?),
                  "1970-01-01"
              )
        ');
        $naoLidasStmt->execute([$outroId, $usuarioId, $usuarioId, $outroId]);
        $naoLidas = (int)$naoLidasStmt->fetch()['total'];

        $conversas[] = [
            'outro_id'         => (int)$outro['codusuario'],
            'outro_nome'       => $outro['nome'],
            'ultima_mensagem'  => $ultima['mensagem']  ?? null,
            'ultima_data'      => $ultima['data_envio'] ?? null,
            'remetente'        => isset($ultima['usuario_codusuario_remetente']) ? (int)$ultima['usuario_codusuario_remetente'] : null,
            'nao_lidas'        => (int)$naoLidas,
        ];
    }

    // Ordenar por última mensagem
    usort($conversas, fn($a, $b) => strcmp($b['ultima_data'] ?? '', $a['ultima_data'] ?? ''));

    echo json_encode(['conversas' => $conversas]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
