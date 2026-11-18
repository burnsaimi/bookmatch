<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados     = json_decode(file_get_contents('php://input'), true);
$usuarioId = (int)($dados['usuario']    ?? 0);
$estanteCod= (int)($dados['estante_cod']?? 0);
$donoId    = (int)($dados['dono_id']    ?? 0);

if (!$usuarioId || !$estanteCod || !$donoId) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

try {
    $pdo = conectar();

    // Evita curtida duplicada
    $dup = $pdo->prepare('SELECT codcurtida FROM curtidas WHERE usuario_codusuario = ? AND estante_cod = ?');
    $dup->execute([$usuarioId, $estanteCod]);
    if ($dup->fetch()) {
        echo json_encode(['sucesso' => true, 'match' => false]);
        exit;
    }

    // Registra a curtida
    $ins = $pdo->prepare('
        INSERT INTO curtidas (datacurtida, usuario_codusuario, estante_cod, usuario_codusuario1)
        VALUES (CURDATE(), ?, ?, ?)
    ');
    $ins->execute([$usuarioId, $estanteCod, $donoId]);
    $curtidaId = (int)$pdo->lastInsertId();

    // Verifica se o dono já curtiu algum livro do usuário atual (match reverso)
    $rev = $pdo->prepare('
        SELECT c.codcurtida FROM curtidas c
        JOIN estante e ON c.estante_cod = e.cod
        WHERE c.usuario_codusuario = ?
          AND e.usuario_codusuario = ?
        LIMIT 1
    ');
    $rev->execute([$donoId, $usuarioId]);
    $curtidaReversa = $rev->fetch();

    $houveMaatch = false;
    if ($curtidaReversa) {
        // Cria o match
        $match = $pdo->prepare('
            INSERT INTO matches (curtidas_codcurtida, curtidas_codcurtida1, datamatch)
            VALUES (?, ?, CURDATE())
        ');
        $match->execute([$curtidaId, $curtidaReversa['codcurtida']]);
        $houveMaatch = true;
    }

    echo json_encode(['sucesso' => true, 'match' => $houveMaatch]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
