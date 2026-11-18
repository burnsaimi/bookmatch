<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados   = json_decode(file_get_contents('php://input'), true);
$nome    = trim($dados['nome']    ?? '');
$email   = trim($dados['email']   ?? '');
$senha   = $dados['senha']  ?? '';
$generos = $dados['generos'] ?? [];
$cidade  = trim($dados['cidade'] ?? '');
$uf      = trim($dados['uf']     ?? '');

if (!$nome || !$email || !$senha) {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha todos os campos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['erro' => 'E-mail inválido.']);
    exit;
}

try {
    $pdo = conectar();

    $check = $pdo->prepare('SELECT codusuario FROM usuario WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['erro' => 'Este e-mail já está cadastrado.']);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO usuario (nome, email, senha, cidade, uf, ativo, tipo_usuario)
         VALUES (?, ?, ?, ?, ?, 'Ativo', 'COMUM')"
    );
    $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $cidade, $uf]);
    $codusuario = (int) $pdo->lastInsertId();

    // Salva gêneros favoritos selecionados no cadastro
    if (!empty($generos) && is_array($generos)) {
        $findGen = $pdo->prepare('SELECT codgen FROM generos WHERE genero = ? LIMIT 1');
        $insGen  = $pdo->prepare('INSERT INTO generos_favo (usuario_codusuario, generos_codgen) VALUES (?, ?)');
        foreach ($generos as $nomeGen) {
            $nomeGen = trim($nomeGen);
            if (!$nomeGen) continue;
            $findGen->execute([$nomeGen]);
            $row = $findGen->fetch();
            if ($row) {
                $insGen->execute([$codusuario, (int)$row['codgen']]);
            }
        }
    }

    $usuario = ['codusuario' => $codusuario, 'nome' => $nome, 'email' => $email];
    echo json_encode(['sucesso' => true, 'usuario' => $usuario]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
