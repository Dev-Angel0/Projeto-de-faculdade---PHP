<?php
// Exclui a divulgação e mantém a publicação sem evento associado.
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: divulgacoes.php?mensagem=' . urlencode('Divulgação inválida') . '&tipo=erro');
    exit;
}

try {
    $pdo = (new Database())->conectar();
    $consulta = $pdo->prepare(
        'SELECT p.id_publicacao, d.id_convidado
         FROM divulgacao d
         JOIN publicacao p ON p.id_divulgacao = d.id_divulgacao
         WHERE d.id_divulgacao = ?'
    );
    $consulta->execute([$id]);
    $vinculo = $consulta->fetch();
    if (!$vinculo) {
        header('Location: divulgacoes.php?mensagem=' . urlencode('Divulgação não encontrada') . '&tipo=erro');
        exit;
    }

    $pdo->beginTransaction();
    $pdo->prepare('UPDATE publicacao SET id_divulgacao = NULL WHERE id_publicacao = ?')
        ->execute([$vinculo['id_publicacao']]);
    $pdo->prepare('DELETE FROM divulgacao WHERE id_divulgacao = ?')->execute([$id]);
    $pdo->prepare(
        'DELETE FROM convidados
         WHERE id_convidado = ?
           AND NOT EXISTS (SELECT 1 FROM divulgacao WHERE id_convidado = ?)'
    )->execute([$vinculo['id_convidado'], $vinculo['id_convidado']]);
    $pdo->commit();
    $mensagem = 'Divulgação excluída; publicação mantida sem evento';
    $tipo = 'sucesso';
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $mensagem = Database::mensagemErro($exception, 'excluir a divulgação e sua publicação');
    $tipo = 'erro';
}

header('Location: divulgacoes.php?mensagem=' . urlencode($mensagem) . '&tipo=' . $tipo);
