<?php
// Exclui um autor somente se ele ainda não estiver associado a publicações.
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: autores.php?mensagem=' . urlencode('Autor inválido') . '&tipo=erro');
    exit;
}

try {
    $pdo = (new Database())->conectar();
    $verificar = $pdo->prepare('SELECT COUNT(*) FROM publicacao_autor WHERE id_autor = ?');
    $verificar->execute([$id]);
    if ((int)$verificar->fetchColumn() > 0) {
        header('Location: autores.php?mensagem=' . urlencode('Este autor está associado a publicações e não pode ser excluído') . '&tipo=erro');
        exit;
    }

    $excluir = $pdo->prepare('DELETE FROM autores WHERE id_autor = ?');
    $excluir->execute([$id]);
    $excluido = $excluir->rowCount() > 0;
    $mensagem = $excluido ? 'Autor excluído' : 'Autor não encontrado';
    $tipo = $excluido ? 'sucesso' : 'erro';
} catch (PDOException $exception) {
    $mensagem = Database::mensagemErro($exception, 'excluir o autor');
    $tipo = 'erro';
}

header('Location: autores.php?mensagem=' . urlencode($mensagem) . '&tipo=' . $tipo);
