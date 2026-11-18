<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ─────────────────────────────────────────────
// GET: lista todos os usuários + contadores
// ─────────────────────────────────────────────
if ($method === 'GET') {
    try {
        $pdo = conectar();
        $stmt = $pdo->query('
            SELECT
                u.codusuario,
                u.nome,
                u.email,
                u.cidade,
                u.uf,
                u.tipo_usuario,
                u.ativo,
                (SELECT COUNT(*) FROM estante e WHERE e.usuario_codusuario = u.codusuario) AS livros
            FROM usuario u
            ORDER BY u.codusuario ASC
        ');
        $usuarios = $stmt->fetchAll();
        echo json_encode(['usuarios' => $usuarios]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$acao  = $dados['acao'] ?? '';

try {
    $pdo = conectar();

    // ─────────────────────────────────────────
    // CRIAR usuário
    // ─────────────────────────────────────────
    if ($method === 'POST' && $acao === 'criar') {
        $nome  = trim($dados['nome']  ?? '');
        $email = trim($dados['email'] ?? '');
        $senha = $dados['senha'] ?? '';
        $role  = ($dados['role'] ?? 'usuario') === 'admin' ? 'ADMIN' : 'COMUM';
        $cidade= trim($dados['cidade'] ?? '');
        $uf    = trim($dados['uf']     ?? '');

        if (!$nome || !$email || !$senha) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome, e-mail e senha são obrigatórios.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            exit;
        }

        $check = $pdo->prepare('SELECT codusuario FROM usuario WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(['erro' => 'Este e-mail já está cadastrado.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO usuario (nome, email, senha, cidade, uf, ativo, tipo_usuario)
            VALUES (?, ?, ?, ?, ?, 'Ativo', ?)
        ");
        $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $cidade, $uf, $role]);
        echo json_encode(['sucesso' => true, 'codusuario' => (int)$pdo->lastInsertId()]);
        exit;
    }

    // ─────────────────────────────────────────
    // EDITAR usuário (nome, email, cidade, uf, role, status, senha opcional)
    // ─────────────────────────────────────────
    if ($method === 'POST' && $acao === 'editar') {
        $id     = (int)($dados['codusuario'] ?? 0);
        $nome   = trim($dados['nome']  ?? '');
        $email  = trim($dados['email'] ?? '');
        $cidade = trim($dados['cidade'] ?? '');
        $uf     = trim($dados['uf']     ?? '');
        $role   = ($dados['role'] ?? 'usuario') === 'admin' ? 'ADMIN' : 'COMUM';
        $status = $dados['status'] ?? 'Ativo';
        $senha  = $dados['senha'] ?? '';

        // mapeia status do front para o enum do banco
        $mapaStatus = ['ativo'=>'Ativo','inativo'=>'Inativo','pausado'=>'Pausado','bloqueado'=>'Bloqueado'];
        $statusEnum = $mapaStatus[strtolower($status)] ?? 'Ativo';

        if (!$id || !$nome || !$email) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados incompletos.']);
            exit;
        }

        $check = $pdo->prepare('SELECT codusuario FROM usuario WHERE email = ? AND codusuario != ?');
        $check->execute([$email, $id]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(['erro' => 'E-mail já usado por outro usuário.']);
            exit;
        }

        if ($senha) {
            $stmt = $pdo->prepare('UPDATE usuario SET nome=?, email=?, cidade=?, uf=?, tipo_usuario=?, ativo=?, senha=? WHERE codusuario=?');
            $stmt->execute([$nome, $email, $cidade, $uf, $role, $statusEnum, password_hash($senha, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuario SET nome=?, email=?, cidade=?, uf=?, tipo_usuario=?, ativo=? WHERE codusuario=?');
            $stmt->execute([$nome, $email, $cidade, $uf, $role, $statusEnum, $id]);
        }
        echo json_encode(['sucesso' => true]);
        exit;
    }

    // ─────────────────────────────────────────
    // STATUS rápido (bloquear / desbloquear / pausar)
    // ─────────────────────────────────────────
    if ($method === 'POST' && $acao === 'status') {
        $id     = (int)($dados['codusuario'] ?? 0);
        $status = $dados['status'] ?? 'Ativo';
        $motivo = $dados['motivo'] ?? null;
        $obs    = $dados['obs'] ?? null;
        $mapaStatus = ['ativo'=>'Ativo','inativo'=>'Inativo','pausado'=>'Pausado','bloqueado'=>'Bloqueado'];
        $statusEnum = $mapaStatus[strtolower($status)] ?? 'Ativo';

        if (!$id) { http_response_code(400); echo json_encode(['erro'=>'ID ausente.']); exit; }

        if ($statusEnum === 'Bloqueado') {
            $stmt = $pdo->prepare('UPDATE usuario SET ativo=?, motivo_bloqueio=?, obs_interna=? WHERE codusuario=?');
            $stmt->execute([$statusEnum, $motivo, $obs ? mb_substr($obs,0,256) : null, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuario SET ativo=? WHERE codusuario=?');
            $stmt->execute([$statusEnum, $id]);
        }
        echo json_encode(['sucesso' => true]);
        exit;
    }

    // ─────────────────────────────────────────
    // EXCLUIR usuário
    // ─────────────────────────────────────────
    if ($method === 'DELETE' || ($method === 'POST' && $acao === 'excluir')) {
        $id = (int)($dados['codusuario'] ?? 0);
        if (!$id) { http_response_code(400); echo json_encode(['erro'=>'ID ausente.']); exit; }
        // Marca como inativo (exclusão lógica) para não quebrar FKs de estante/curtidas/mensagem
        $stmt = $pdo->prepare("UPDATE usuario SET ativo='Inativo' WHERE codusuario=?");
        $stmt->execute([$id]);
        echo json_encode(['sucesso' => true, 'modo' => 'inativado']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['erro' => 'Ação não reconhecida.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e->getMessage()]);
}
