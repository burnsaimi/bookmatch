<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados    = json_decode(file_get_contents('php://input'), true);
$usuarioId= (int)($dados['usuario']       ?? 0);
$outroId  = (int)($dados['outro_usuario'] ?? 0);

if (!$usuarioId || !$outroId) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

try {
    $pdo = conectar();

    // Curtida que o outro já fez na estante do usuário atual
    $curtidaOutroStmt = $pdo->prepare('
        SELECT c.codcurtida FROM curtidas c
        JOIN estante e ON c.estante_cod = e.cod
        WHERE c.usuario_codusuario = ? AND e.usuario_codusuario = ?
        LIMIT 1
    ');
    $curtidaOutroStmt->execute([$outroId, $usuarioId]);
    $curtidaOutro = $curtidaOutroStmt->fetch();

    if (!$curtidaOutro) {
        echo json_encode(['sucesso' => false, 'erro' => 'Curtida original não encontrada.']);
        exit;
    }

    // Pega qualquer livro do outro usuário para curtir de volta
    $estanteStmt = $pdo->prepare('SELECT cod FROM estante WHERE usuario_codusuario = ? LIMIT 1');
    $estanteStmt->execute([$outroId]);
    $estanteOutro = $estanteStmt->fetch();

    if (!$estanteOutro) {
        echo json_encode(['sucesso' => false, 'erro' => 'Outro usuário não tem livros na estante.']);
        exit;
    }

    $estanteCod = (int)$estanteOutro['cod'];

    // Verifica se já curtiu esse livro
    $dupStmt = $pdo->prepare('SELECT codcurtida FROM curtidas WHERE usuario_codusuario = ? AND estante_cod = ?');
    $dupStmt->execute([$usuarioId, $estanteCod]);
    $curtidaAtual = $dupStmt->fetch();

    if (!$curtidaAtual) {
        $insStmt = $pdo->prepare('
            INSERT INTO curtidas (datacurtida, usuario_codusuario, estante_cod, usuario_codusuario1)
            VALUES (CURDATE(), ?, ?, ?)
        ');
        $insStmt->execute([$usuarioId, $estanteCod, $outroId]);
        $curtidaAtualId = (int)$pdo->lastInsertId();
    } else {
        $curtidaAtualId = (int)$curtidaAtual['codcurtida'];
    }

    // Verifica se match já existe
    $matchExiste = $pdo->prepare('
        SELECT codmacth FROM matches
        WHERE (curtidas_codcurtida = ? AND curtidas_codcurtida1 = ?)
           OR (curtidas_codcurtida = ? AND curtidas_codcurtida1 = ?)
    ');
    $matchExiste->execute([
        $curtidaAtualId, $curtidaOutro['codcurtida'],
        $curtidaOutro['codcurtida'], $curtidaAtualId,
    ]);

    if (!$matchExiste->fetch()) {
        $pdo->prepare('INSERT INTO matches (curtidas_codcurtida, curtidas_codcurtida1, datamatch) VALUES (?, ?, CURDATE())')
            ->execute([$curtidaAtualId, $curtidaOutro['codcurtida']]);
    }

    echo json_encode(['sucesso' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor.']);
}
