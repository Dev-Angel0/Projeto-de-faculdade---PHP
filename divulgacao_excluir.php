<?php
// Exclui somente a divulgação e mantém a publicação vinculada ao sistema.
// Carrega a configuração necessária para acessar o banco.
require_once 'config/database.php';

// Converte o identificador informado na URL para inteiro.
$id = (int)($_GET['id'] ?? 0);
// Impede a exclusão quando o identificador é inválido.
if ($id <= 0) {
    header('Location: divulgacoes.php?mensagem=' . urlencode('Divulgação inválida') . '&tipo=erro');
    exit;
}

try {
    // Abre a conexão com o banco.
    $pdo = (new Database())->conectar();
    // Localiza a publicação e o convidado associados à divulgação.
    $consulta = $pdo->prepare(
        'SELECT p.id_publicacao, d.id_convidado
         FROM divulgacao d
         JOIN publicacao p ON p.id_divulgacao = d.id_divulgacao
         WHERE d.id_divulgacao = ?'
    );
    $consulta->execute([$id]);
    // Recupera os vínculos necessários para limpar os dados relacionados.
    $vinculo = $consulta->fetch();
    // Interrompe o processo quando a divulgação não existe.
    if (!$vinculo) {
        header('Location: divulgacoes.php?mensagem=' . urlencode('Divulgação não encontrada') . '&tipo=erro');
        exit;
    }

    // Inicia a transação para retirar todos os vínculos de forma segura.
    $pdo->beginTransaction();
    // Remove o identificador da divulgação da publicação correspondente.
    $pdo->prepare('UPDATE publicacao SET id_divulgacao = NULL WHERE id_publicacao = ?')
        ->execute([$vinculo['id_publicacao']]);
    // Exclui o registro do evento de divulgação.
    $pdo->prepare('DELETE FROM divulgacao WHERE id_divulgacao = ?')->execute([$id]);
    // Remove o convidado caso ele não esteja ligado a outro evento.
    $pdo->prepare(
        'DELETE FROM convidados
         WHERE id_convidado = ?
           AND NOT EXISTS (SELECT 1 FROM divulgacao WHERE id_convidado = ?)'
    )->execute([$vinculo['id_convidado'], $vinculo['id_convidado']]);
    // Confirma a alteração completa no banco.
    $pdo->commit();
    // Prepara o aviso de que a publicação permaneceu cadastrada.
    $mensagem = 'Divulgação excluída; publicação mantida sem evento';
    $tipo = 'sucesso';
// Captura erros durante a busca ou exclusão dos dados.
} catch (PDOException $exception) {
    // Cancela a transação caso ela ainda esteja ativa.
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Obtém uma mensagem objetiva para mostrar na página.
    $mensagem = Database::mensagemErro($exception, 'excluir a divulgação e sua publicação');
    $tipo = 'erro';
}

// Volta à lista de divulgações com a mensagem do resultado.
header('Location: divulgacoes.php?mensagem=' . urlencode($mensagem) . '&tipo=' . $tipo);
