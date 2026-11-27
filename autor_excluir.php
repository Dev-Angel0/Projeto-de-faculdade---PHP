<?php
// Processa a exclusão de um autor pelo identificador recebido pela URL.

// Carrega a classe responsável pela conexão com o banco de dados.
require_once 'config/database.php';

// Converte o parâmetro "id" da requisição para inteiro e usa zero quando ele não existe.
$id = (int)($_GET['id'] ?? 0);

// Interrompe a operação caso o identificador recebido não seja válido.
if ($id <= 0) {
    // Redireciona para a lista informando que o identificador do autor é inválido.
    header('Location: autores.php?mensagem=' . urlencode('Autor inválido') . '&tipo=erro');
    // Impede que o restante do arquivo seja executado após o redirecionamento.
    exit;
}

// Tenta realizar a consulta e a exclusão sem continuar o fluxo se ocorrer um erro no banco.
try {
    // Cria a conexão PDO com o banco de dados.
    $pdo = (new Database())->conectar();

    // Prepara a consulta que verifica se o autor possui publicações associadas.
    $verificar = $pdo->prepare('SELECT COUNT(*) FROM publicacao_autor WHERE id_autor = ?');

    // Executa a consulta utilizando o identificador informado.
    $verificar->execute([$id]);

    // Impede a exclusão quando existe pelo menos uma publicação associada ao autor.
    if ((int)$verificar->fetchColumn() > 0) {
        // Redireciona para a lista com uma mensagem explicando a restrição.
        header('Location: autores.php?mensagem=' . urlencode('Este autor está associado a publicações e não pode ser excluído') . '&tipo=erro');
        // Encerra o processo para não executar o comando de exclusão.
        exit;
    }

    // Prepara o comando SQL responsável pela exclusão do autor.
    $excluir = $pdo->prepare('DELETE FROM autores WHERE id_autor = ?');

    // Executa a exclusão com o identificador informado.
    $excluir->execute([$id]);

    // Confirma o sucesso comparando a quantidade de registros removidos.
    $excluido = $excluir->rowCount() > 0;

    // Define a mensagem exibida conforme o autor tenha sido encontrado ou não.
    $mensagem = $excluido ? 'Autor excluído' : 'Autor não encontrado';

    // Define o tipo visual da mensagem: sucesso ou erro.
    $tipo = $excluido ? 'sucesso' : 'erro';

    // Captura falhas específicas de acesso ou execução no banco de dados.
} catch (PDOException $exception) {
    // Converte a exceção em uma mensagem segura para a interface.
    $mensagem = Database::mensagemErro($exception, 'excluir o autor');

    // Marca a operação como falha para exibir a mensagem correta.
    $tipo = 'erro';
}

// Redireciona para a lista de autores com o resultado da operação.
header('Location: autores.php?mensagem=' . urlencode($mensagem) . '&tipo=' . $tipo);
