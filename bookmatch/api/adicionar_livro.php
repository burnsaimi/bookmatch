<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados     = json_decode(file_get_contents('php://input'), true);
$usuarioId = (int)($dados['usuario']  ?? 0);
$titulo    = trim($dados['titulo']    ?? '');
$autor     = trim($dados['autor']     ?? '');
$genero    = trim($dados['genero']    ?? '—');
$editora   = trim($dados['editora']   ?? '');
$ano       = trim($dados['ano']       ?? '');
$foto      = trim($dados['foto']      ?? '');
$destino   = $dados['destino'] ?? 'estante'; // 'estante' ou 'queroler'

if (!$usuarioId || !$titulo) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados incompletos.']);
    exit;
}

try {
    $pdo = conectar();

    // Garante que o usuário da sessão ainda existe (evita erro de FK confuso)
    $chkUser = $pdo->prepare('SELECT 1 FROM usuario WHERE codusuario = ?');
    $chkUser->execute([$usuarioId]);
    if (!$chkUser->fetch()) {
        http_response_code(401);
        echo json_encode(['erro' => 'Sua sessão expirou ou o usuário não existe mais. Faça login novamente.']);
        exit;
    }

    // ano: a coluna data_lancamento é YEAR no schema novo — usa só 4 dígitos ou null
    $anoYear = (preg_match('/\d{4}/', $ano, $m)) ? $m[0] : null;
    // isbn é varchar(13) NOT NULL; limpa hífens/espaços e limita a 13 chars
    $isbn = strtoupper(preg_replace('/[^0-9Xx]/', '', $dados['isbn'] ?? ''));
    $isbn = substr($isbn, 0, 13);
    // se não veio ISBN da Open Library, gera um placeholder determinístico
    if ($isbn === '') $isbn = substr(preg_replace('/\D/', '', md5($titulo . $foto)), 0, 13);

    // Trunca campos para respeitar os limites das colunas (genero varchar(30), editora varchar(45))
    $genero  = mb_substr($genero, 0, 30);
    $editora = mb_substr($editora, 0, 45);

    // Reusa o livro: primeiro pelo ISBN (mais confiável), depois por título
    $findStmt = $pdo->prepare('SELECT codlivro FROM livro WHERE isbn = ? LIMIT 1');
    $findStmt->execute([$isbn]);
    $livro = $findStmt->fetch();
    if (!$livro) {
        $findByName = $pdo->prepare('SELECT codlivro FROM livro WHERE nome = ? LIMIT 1');
        $findByName->execute([$titulo]);
        $livro = $findByName->fetch();
    }

    if ($livro) {
        $codlivro = (int)$livro['codlivro'];
    } else {
        // Insere o livro
        $insLivro = $pdo->prepare('
            INSERT INTO livro (isbn, nome, editora, foto_api, genero, data_lancamento)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $insLivro->execute([$isbn, $titulo, $editora ?: null, $foto ?: null, $genero, $anoYear]);
        $codlivro = (int)$pdo->lastInsertId();

        // Insere autor se informado
        if ($autor) {
            $findAutor = $pdo->prepare('SELECT cod_autor FROM autores WHERE nome = ? LIMIT 1');
            $findAutor->execute([$autor]);
            $autorRow = $findAutor->fetch();

            if ($autorRow) {
                $codAutor = (int)$autorRow['cod_autor'];
            } else {
                $insAutor = $pdo->prepare('INSERT INTO autores (nome) VALUES (?)');
                $insAutor->execute([$autor]);
                $codAutor = (int)$pdo->lastInsertId();
            }

            $pdo->prepare('INSERT INTO escreveu (livro_codlivro, autores_cod_autor) VALUES (?, ?)')
                ->execute([$codlivro, $codAutor]);
        }
    }

    if ($destino === 'queroler') {
        // Verifica se já está na lista
        $dup = $pdo->prepare('SELECT cod_quer_ler FROM livros_quer_ler WHERE usuario_codusuario = ? AND livro_codlivro = ?');
        $dup->execute([$usuarioId, $codlivro]);
        if (!$dup->fetch()) {
            $pdo->prepare('INSERT INTO livros_quer_ler (usuario_codusuario, livro_codlivro) VALUES (?, ?)')
                ->execute([$usuarioId, $codlivro]);
        }
    } else {
        // Verifica se já está na estante
        $dup = $pdo->prepare('SELECT cod FROM estante WHERE usuario_codusuario = ? AND livro_codlivro = ?');
        $dup->execute([$usuarioId, $codlivro]);
        if (!$dup->fetch()) {
            $pdo->prepare('INSERT INTO estante (data_adicionado, livro_codlivro, usuario_codusuario) VALUES (NOW(), ?, ?)')
                ->execute([$codlivro, $usuarioId]);
        }
    }

    echo json_encode(['sucesso' => true, 'codlivro' => $codlivro]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor: ' . $e->getMessage()]);
}
