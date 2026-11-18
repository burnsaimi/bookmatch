<?php
require_once 'config.php';

// GET: lista todas as denúncias (para o painel admin)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = conectar();
        $stmt = $pdo->query('
            SELECT
                d.cod_denuncia,
                d.motivo_princi_denuncia AS motivo,
                d.detalhes_denuncia      AS detalhes,
                d.tipo_de_denuncia       AS tipo,
                d.codusuario,
                u.nome                   AS denunciante,
                d.codestante
            FROM denuncia d
            JOIN usuario u ON d.codusuario = u.codusuario
            ORDER BY d.cod_denuncia DESC
        ');
        echo json_encode(['denuncias' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

// DELETE: resolve/ignora uma denúncia (remove do banco — a tabela não tem coluna de status)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $dados = json_decode(file_get_contents('php://input'), true);
    $cod   = (int)($dados['cod_denuncia'] ?? 0);
    if (!$cod) {
        http_response_code(400);
        echo json_encode(['erro' => 'Denúncia não informada.']);
        exit;
    }
    try {
        $pdo = conectar();
        $pdo->prepare('DELETE FROM denuncia WHERE cod_denuncia = ?')->execute([$cod]);
        echo json_encode(['sucesso' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados      = json_decode(file_get_contents('php://input'), true);
$usuarioId  = (int)($dados['usuario']  ?? 0);   // quem está denunciando
$tipo       = trim($dados['tipo']      ?? '');   // 'livro' ou 'usuario'
$motivo     = trim($dados['motivo']    ?? '');
$detalhes   = trim($dados['detalhes']  ?? '');
$estanteCod = isset($dados['estante_cod']) ? (int)$dados['estante_cod'] : null;

if (!$usuarioId || !$motivo) {
    http_response_code(400);
    echo json_encode(['erro' => 'Informe o motivo da denúncia.']);
    exit;
}

// Mapeia o tipo curto do front para o enum exato do banco
$mapaTipo = [
    'livro'   => 'Livro inadequado (Capa, descrição ou informações erradas)',
    'usuario' => 'Usuário (Comportamento ou perfil suspeito)',
];
$tipoEnum = $mapaTipo[$tipo] ?? null;

// Mapeia o motivo para o enum exato do banco (com fallback)
$motivosValidos = [
    'Conteúdo impróprio'             => 'Conteúdo impróprio / ofensivo',
    'Conteúdo impróprio / ofensivo'  => 'Conteúdo impróprio / ofensivo',
    'Comportamento inadequado'       => 'Comportamento inadequado',
    'Spam'                           => 'Spam',
    'Fraude ou golpe'                => 'Suspeita de fraude ou golpeOutro motivo',
    'Outro'                          => 'Suspeita de fraude ou golpeOutro motivo',
];
$motivoEnum = $motivosValidos[$motivo] ?? 'Comportamento inadequado';

$detalhes = mb_substr($detalhes, 0, 256);

try {
    $pdo = conectar();

    // Valida usuário
    $chk = $pdo->prepare('SELECT 1 FROM usuario WHERE codusuario = ?');
    $chk->execute([$usuarioId]);
    if (!$chk->fetch()) {
        http_response_code(401);
        echo json_encode(['erro' => 'Sessão inválida. Faça login novamente.']);
        exit;
    }

    // Se veio estante_cod, confirma que existe; senão grava NULL
    if ($estanteCod) {
        $chkEst = $pdo->prepare('SELECT 1 FROM estante WHERE cod = ?');
        $chkEst->execute([$estanteCod]);
        if (!$chkEst->fetch()) $estanteCod = null;
    }

    $stmt = $pdo->prepare('
        INSERT INTO denuncia
            (motivo_princi_denuncia, detalhes_denuncia, tipo_de_denuncia, codusuario, codestante)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$motivoEnum, $detalhes ?: null, $tipoEnum, $usuarioId, $estanteCod]);

    echo json_encode(['sucesso' => true, 'cod_denuncia' => (int)$pdo->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e->getMessage()]);
}
