<?php
// Remove a publicação e, se existir, sua divulgação e convidado.
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?mensagem=' . urlencode('Publicação inválida') . '&tipo=erro');
    exit;
}

try {
    $pdo = (new Database())->conectar();
    $consulta = $pdo->prepare(
        'SELECT p.id_divulgacao, d.id_convidado
         FROM publicacao p
         LEFT JOIN divulgacao d ON d.id_divulgacao = p.id_divulgacao
         WHERE p.id_publicacao = ?'
    );
    $consulta->execute([$id]);
    $vinculo = $consulta->fetch();
    if (!$vinculo) {
        header('Location: index.php?mensagem=' . urlencode('Publicação não encontrada') . '&tipo=erro');
        exit;
    }

    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM publicacao WHERE id_publicacao = ?')->execute([$id]);
    if ($vinculo['id_divulgacao'] !== null) {
        $pdo->prepare('DELETE FROM divulgacao WHERE id_divulgacao = ?')->execute([$vinculo['id_divulgacao']]);
        $pdo->prepare(
            'DELETE FROM convidados
             WHERE id_convidado = ?
               AND NOT EXISTS (SELECT 1 FROM divulgacao WHERE id_convidado = ?)'
        )->execute([$vinculo['id_convidado'], $vinculo['id_convidado']]);
    }
    $pdo->commit();
    $mensagem = $vinculo['id_divulgacao'] !== null
        ? 'Publicação e divulgação excluídas'
        : 'Publicação excluída';
    $tipo = 'sucesso';
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $mensagem = Database::mensagemErro($exception, 'excluir a publicação e sua divulgação');
    $tipo = 'erro';
}

header('Location: index.php?mensagem=' . urlencode($mensagem) . '&tipo=' . $tipo);
