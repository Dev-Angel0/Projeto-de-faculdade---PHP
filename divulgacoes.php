<?php
// Lista os eventos de divulgação junto com o livro e o convidado associados.
require_once 'config/database.php';

$divulgacoes = [];
$erro = '';
$mensagem = $_GET['mensagem'] ?? '';
$tipoMensagem = $_GET['tipo'] ?? 'sucesso';

try {
    $pdo = (new Database())->conectar();
    $divulgacoes = $pdo->query(
        'SELECT d.*, c.nome AS convidado, c.email, p.id_publicacao, p.titulo
         FROM divulgacao d
         JOIN convidados c ON c.id_convidado = d.id_convidado
         JOIN publicacao p ON p.id_divulgacao = d.id_divulgacao
         ORDER BY d.data_evento DESC'
    )->fetchAll();
} catch (PDOException $exception) {
    $erro = Database::mensagemErro($exception, 'carregar as divulgações');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Divulgações</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <h1>Divulgações agendadas</h1>
    <nav><a href="frontend/index.html">Página inicial</a><a href="index.php">Publicações</a><a href="livro_form.php">Cadastrar publicação</a></nav>
</header>
<main>
    <?php if ($mensagem !== ''): ?><p class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : '' ?>"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro !== ''): ?><p class="mensagem erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
    <section class="card">
        <h2>Divulgações cadastradas: <?= count($divulgacoes) ?></h2>
        <p>Consulte, edite ou exclua eventos. Excluir um evento mantém a publicação cadastrada.</p>
        <a class="button" href="livro_form.php">Cadastrar publicação (evento opcional)</a>
    </section>
    <table>
        <thead><tr><th>Publicação</th><th>Convidado</th><th>E-mail</th><th>Data</th><th>Local</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($divulgacoes as $divulgacao): ?>
            <tr>
                <td><?= htmlspecialchars($divulgacao['titulo']) ?></td>
                <td><?= htmlspecialchars($divulgacao['convidado']) ?></td>
                <td><?= htmlspecialchars($divulgacao['email']) ?></td>
                <td><?= date('d/m/Y', strtotime($divulgacao['data_evento'])) ?></td>
                <td><?= htmlspecialchars($divulgacao['local_evento']) ?></td>
                <td>
                    <a class="button" href="divulgacao_form.php?id=<?= $divulgacao['id_divulgacao'] ?>">Editar evento</a>
                    <a class="button danger" href="divulgacao_excluir.php?id=<?= $divulgacao['id_divulgacao'] ?>" onclick="return confirm('Excluir esta divulgação? A publicação será mantida sem evento.')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
