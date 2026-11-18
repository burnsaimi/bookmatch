<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $usuarioId = (int)($_GET['usuario'] ?? 0);
    $outroId   = (int)($_GET['outro']   ?? 0);

    if (!$usuarioId || !$outroId) {
        http_response_code(400);
        echo json_encode(['erro' => 'Parâmetros incompletos.']);
        exit;
    }

    try {
        $pdo = conectar();
        $stmt = $pdo->prepare('
            SELECT
                codmensagem,
                mensagem,
                data_envio,
                usuario_codusuario_remetente AS remetente
            FROM mensagem
            WHERE (usuario_codusuario_remetente = ? AND usuario_codusuario_destinatario = ?)
               OR (usuario_codusuario_remetente = ? AND usuario_codusuario_destinatario = ?)
            ORDER BY data_envio ASC
        ');
        $stmt->execute([$usuarioId, $outroId, $outroId, $usuarioId]);
        echo json_encode(['mensagens' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

if ($method === 'POST') {
    $dados        = json_decode(file_get_contents('php://input'), true);
    $remetente    = (int)($dados['remetente']    ?? 0);
    $destinatario = (int)($dados['destinatario'] ?? 0);
    $texto        = trim($dados['mensagem']       ?? '');

    if (!$remetente || !$destinatario || $texto === '') {
        http_response_code(400);
        echo json_encode(['erro' => 'Dados incompletos.']);
        exit;
    }

    try {
        $pdo = conectar();
        $stmt = $pdo->prepare('
            INSERT INTO mensagem
                (usuario_codusuario_remetente, usuario_codusuario_destinatario, mensagem, data_envio)
            VALUES (?, ?, ?, NOW())
        ');
        $stmt->execute([$remetente, $destinatario, $texto]);
        echo json_encode(['sucesso' => true, 'id' => (int)$pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);
