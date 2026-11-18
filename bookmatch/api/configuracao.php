<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $usuarioId = (int)($_GET['usuario'] ?? 0);
    if (!$usuarioId) {
        http_response_code(400);
        echo json_encode(['erro' => 'Usuário não informado.']);
        exit;
    }

    try {
        $pdo  = conectar();
        $stmt = $pdo->prepare('SELECT * FROM configuracao WHERE usuario_codusuario = ?');
        $stmt->execute([$usuarioId]);
        $config = $stmt->fetch();

        if (!$config) {
            // Retorna padrões se não existir ainda
            $config = [
                'estante_publica'    => 1,
                'mostrar_localizacao'=> 0,
                'mostrar_avaliacao'  => 1,
                'ordenar_por'        => 'titulo',
                'usuario_codusuario' => $usuarioId,
            ];
        }

        // Busca gêneros favoritos do usuário
        $genStmt = $pdo->prepare('
            SELECT g.genero FROM generos_favo gf
            JOIN generos g ON gf.generos_codgen = g.codgen
            WHERE gf.usuario_codusuario = ?
        ');
        $genStmt->execute([$usuarioId]);
        $generos = $genStmt->fetchAll(PDO::FETCH_COLUMN);

        $config['generos_favoritos'] = $generos;

        echo json_encode($config);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

if ($method === 'POST') {
    $dados     = json_decode(file_get_contents('php://input'), true);
    $usuarioId = (int)($dados['usuario'] ?? 0);

    if (!$usuarioId) {
        http_response_code(400);
        echo json_encode(['erro' => 'Usuário não informado.']);
        exit;
    }

    try {
        $pdo = conectar();

        $estantePublica    = isset($dados['estante_publica'])     ? (int)$dados['estante_publica']     : 1;
        $mostrarLocal      = isset($dados['mostrar_localizacao'])  ? (int)$dados['mostrar_localizacao']  : 0;
        $mostrarAval       = isset($dados['mostrar_avaliacao'])    ? (int)$dados['mostrar_avaliacao']    : 1;
        $ordenarPor        = $dados['ordenar_por'] ?? 'titulo';

        // Upsert
        $check = $pdo->prepare('SELECT COUNT(*) FROM configuracao WHERE usuario_codusuario = ?');
        $check->execute([$usuarioId]);
        $existe = (int)$check->fetchColumn();

        if ($existe) {
            $stmt = $pdo->prepare('
                UPDATE configuracao
                SET estante_publica = ?, mostrar_localizacao = ?, mostrar_avaliacao = ?, ordenar_por = ?
                WHERE usuario_codusuario = ?
            ');
            $stmt->execute([$estantePublica, $mostrarLocal, $mostrarAval, $ordenarPor, $usuarioId]);
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO configuracao (estante_publica, mostrar_localizacao, mostrar_avaliacao, ordenar_por, usuario_codusuario)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$estantePublica, $mostrarLocal, $mostrarAval, $ordenarPor, $usuarioId]);
        }

        echo json_encode(['sucesso' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro no servidor.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);
