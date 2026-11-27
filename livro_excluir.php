<?php
// Remove uma publicação e limpa também seus dados de divulgação associados.
// Carrega os recursos de conexão com o banco.
require_once 'config/database.php';

// Obtém o identificador da publicação enviado pela URL.
$id = (int)($_GET['id'] ?? 0);
// Valida o identificador antes de acessar o banco.
if ($id <= 0) {
    header('Location: index.php?mensagem=' . urlencode('Publicação inválida') . '&tipo=erro');
    exit;
}

// Agrupa em uma tentativa segura todas as operações de consulta e exclusão.
try {
    // Abre a conexão usada pelas próximas consultas.
    $pdo = (new Database())->conectar();
    // Busca a divulgação e o convidado ligados à publicação.
    $consulta = $pdo->prepare(
        'SELECT p.id_divulgacao, d.id_convidado
         FROM publicacao p
         LEFT JOIN divulgacao d ON d.id_divulgacao = p.id_divulgacao
         WHERE p.id_publicacao = ?'
    );
    // Executa a consulta pelo identificador recebido.
    $consulta->execute([$id]);
    // Recupera o vínculo encontrado no banco.
    $vinculo = $consulta->fetch();
    // Interrompe a operação quando a publicação não existe.
    if (!$vinculo) {
        header('Location: index.php?mensagem=' . urlencode('Publicação não encontrada') . '&tipo=erro');
        exit;
    }

    // Inicia uma transação para manter todos os dados consistentes.
    $pdo->beginTransaction();
    // Remove a publicação da tabela principal.
    $pdo->prepare('DELETE FROM publicacao WHERE id_publicacao = ?')->execute([$id]);
    // Remove os registros adicionais somente quando a publicação tinha divulgação.
    if ($vinculo['id_divulgacao'] !== null) {
        // Exclui o evento de divulgação associado.
        $pdo->prepare('DELETE FROM divulgacao WHERE id_divulgacao = ?')->execute([$vinculo['id_divulgacao']]);
        // Exclui o convidado apenas quando nenhuma outra divulgação o utiliza.
        $pdo->prepare(
            'DELETE FROM convidados
             WHERE id_convidado = ?
               AND NOT EXISTS (SELECT 1 FROM divulgacao WHERE id_convidado = ?)'
        )->execute([$vinculo['id_convidado'], $vinculo['id_convidado']]);
    }
    // Confirma todas as exclusões da transação.
    $pdo->commit();
    // Define a mensagem conforme exista ou não uma divulgação ligada.
    $mensagem = $vinculo['id_divulgacao'] !== null
        ? 'Publicação e divulgação excluídas'
        : 'Publicação excluída';
    // Marca o processo como concluído com sucesso.
    $tipo = 'sucesso';

// Trata falhas do banco e desfaz alterações ainda pendentes.
} catch (PDOException $exception) {
    // Reverte a transação caso alguma exclusão tenha iniciado uma operação incompleta.
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Gera uma mensagem segura para o usuário.
    $mensagem = Database::mensagemErro($exception, 'excluir a publicação e sua divulgação');
    // Marca o resultado como erro.
    $tipo = 'erro';
}

// Retorna à página inicial com o resultado da operação.
header('Location: index.php?mensagem=' . urlencode($mensagem) . '&tipo=' . $tipo);
