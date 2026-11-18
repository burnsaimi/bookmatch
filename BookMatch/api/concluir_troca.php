<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados      = json_decode(file_get_contents('php://input'), true);
$usuarioId  = (int)($dados['usuario']       ?? 0);  // quem confirma a troca
$outroId    = (int)($dados['outro_usuario'] ?? 0);  // o outro leitor do match
$notaLeitor = (int)($dados['nota_leitor']   ?? 0);
$notaEnvio  = (int)($dados['nota_envio']    ?? 0);

if (!$usuarioId || !$outroId) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

try {
    $pdo = conectar();

    // Valida usuários
    $chk = $pdo->prepare('SELECT 1 FROM usuario WHERE codusuario = ?');
    $chk->execute([$usuarioId]);
    if (!$chk->fetch()) {
        http_response_code(401);
        echo json_encode(['erro' => 'Sessão inválida. Faça login novamente.']);
        exit;
    }

    // Confirma que existe um match entre os dois (segurança mínima)
    $matchStmt = $pdo->prepare('
        SELECT 1
        FROM matches m
        JOIN curtidas c1 ON m.curtidas_codcurtida  = c1.codcurtida
        JOIN curtidas c2 ON m.curtidas_codcurtida1 = c2.codcurtida
        WHERE (c1.usuario_codusuario = :u AND c2.usuario_codusuario = :o)
           OR (c1.usuario_codusuario = :o AND c2.usuario_codusuario = :u)
        LIMIT 1
    ');
    $matchStmt->execute([':u' => $usuarioId, ':o' => $outroId]);
    if (!$matchStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['erro' => 'Nenhum match encontrado com este usuário.']);
        exit;
    }

    // Conclui a troca: remove da estante de troca de AMBOS os livros que
    // o outro curtiu (ou seja, os livros efetivamente trocados).
    // 1) Livros do usuário atual que o OUTRO curtiu
    $removidos = 0;
    $livrosTrocados = $pdo->prepare('
        SELECT DISTINCT e.cod
        FROM curtidas c
        JOIN estante e ON c.estante_cod = e.cod
        WHERE c.usuario_codusuario = ? AND e.usuario_codusuario = ?
    ');

    // livros do usuário atual curtidos pelo outro
    $livrosTrocados->execute([$outroId, $usuarioId]);
    $codsA = $livrosTrocados->fetchAll(PDO::FETCH_COLUMN);

    // livros do outro curtidos pelo usuário atual
    $livrosTrocados->execute([$usuarioId, $outroId]);
    $codsB = $livrosTrocados->fetchAll(PDO::FETCH_COLUMN);

    $delEstante = $pdo->prepare('DELETE FROM estante WHERE cod = ?');
    foreach (array_merge($codsA, $codsB) as $cod) {
        $delEstante->execute([(int)$cod]);
        $removidos++;
    }

    echo json_encode([
        'sucesso'   => true,
        'removidos' => $removidos,
        'avaliacao' => ['leitor' => $notaLeitor, 'envio' => $notaEnvio],
        'mensagem'  => 'Troca concluída.',
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e->getMessage()]);
}
