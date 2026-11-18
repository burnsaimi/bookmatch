<?php
require_once 'config.php';

$usuarioId = (int)($_GET['usuario'] ?? 0);
if (!$usuarioId) {
    http_response_code(400);
    echo 'Usuário não informado.';
    exit;
}

try {
    $pdo = conectar();

    $stmt = $pdo->prepare('
        SELECT
            l.nome        AS titulo,
            a.nome        AS autor,
            l.genero,
            l.editora,
            l.data_lancamento AS ano,
            e.data_adicionado
        FROM estante e
        JOIN livro   l  ON e.livro_codlivro = l.codlivro
        LEFT JOIN escreveu es ON es.livro_codlivro = l.codlivro
        LEFT JOIN autores   a  ON es.autores_cod_autor = a.cod_autor
        WHERE e.usuario_codusuario = ?
        ORDER BY e.data_adicionado DESC
    ');
    $stmt->execute([$usuarioId]);
    $livros = $stmt->fetchAll();

    // Headers para forçar download do arquivo
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="minha_estante.csv"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');

    // BOM UTF-8 para o Excel abrir corretamente
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Cabeçalho
    fputcsv($out, ['Título', 'Autor', 'Gênero', 'Editora', 'Ano', 'Data Adicionado'], ';');

    foreach ($livros as $l) {
        fputcsv($out, [
            $l['titulo']         ?? '',
            $l['autor']          ?? '',
            $l['genero']         ?? '',
            $l['editora']        ?? '',
            $l['ano']            ?? '',
            $l['data_adicionado'] ? substr($l['data_adicionado'], 0, 10) : '',
        ], ';');
    }

    fclose($out);

} catch (Exception $e) {
    http_response_code(500);
    echo 'Erro ao exportar dados.';
}
