<?php
// Edita os dados do evento e de seu convidado.
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
$erro = '';
$divulgacao = [
    'id_convidado' => 0,
    'convidado' => '',
    'email' => '',
    'titulo' => '',
    'data_evento' => '',
    'local_evento' => ''
];
$encontrada = false;

try {
    $pdo = (new Database())->conectar();
    $consulta = $pdo->prepare(
        'SELECT d.*, c.id_convidado, c.nome AS convidado, c.email, p.titulo
         FROM divulgacao d
         JOIN convidados c ON c.id_convidado = d.id_convidado
         JOIN publicacao p ON p.id_divulgacao = d.id_divulgacao
         WHERE d.id_divulgacao = ?'
    );
    $consulta->execute([$id]);
    $dados = $consulta->fetch();
    if (!$dados) {
        header('Location: divulgacoes.php?mensagem=' . urlencode('Divulgação não encontrada') . '&tipo=erro');
        exit;
    }
    $divulgacao = $dados;
    $encontrada = true;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $divulgacao['convidado'] = is_string($_POST['convidado'] ?? null) ? trim($_POST['convidado']) : '';
        $divulgacao['email'] = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        $divulgacao['data_evento'] = is_string($_POST['data_evento'] ?? null) ? $_POST['data_evento'] : '';
        $divulgacao['local_evento'] = is_string($_POST['local_evento'] ?? null) ? trim($_POST['local_evento']) : '';
        $dataEvento = DateTime::createFromFormat('!Y-m-d', $divulgacao['data_evento']);
        $errosData = DateTime::getLastErrors();
        $dataValida = $dataEvento && (!$errosData || ($errosData['warning_count'] === 0 && $errosData['error_count'] === 0));

        if ($divulgacao['convidado'] === '' || !filter_var($divulgacao['email'], FILTER_VALIDATE_EMAIL)
            || !$dataValida || $divulgacao['local_evento'] === '') {
            $erro = 'Informe nome, e-mail válido, data correta e local para a divulgação.';
        } else {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE convidados SET nome = ?, email = ? WHERE id_convidado = ?')->execute([
                $divulgacao['convidado'], $divulgacao['email'], $divulgacao['id_convidado']
            ]);
            $pdo->prepare('UPDATE divulgacao SET data_evento = ?, local_evento = ? WHERE id_divulgacao = ?')->execute([
                $divulgacao['data_evento'], $divulgacao['local_evento'], $id
            ]);
            $pdo->commit();
            header('Location: divulgacoes.php?mensagem=' . urlencode('Divulgação atualizada'));
            exit;
        }
    }
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $erro = Database::mensagemErro($exception, 'carregar ou atualizar a divulgação');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar divulgação</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header><h1>Editar divulgação</h1><nav><a href="divulgacoes.php">Divulgações</a></nav></header>
<main>
    <?php if ($erro !== ''): ?><p class="mensagem erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
    <?php if ($encontrada): ?>
        <form method="post">
            <label for="titulo">Publicação</label>
            <input id="titulo" value="<?= htmlspecialchars($divulgacao['titulo']) ?>" disabled>
            <label for="convidado">Convidado</label>
            <input id="convidado" name="convidado" value="<?= htmlspecialchars($divulgacao['convidado']) ?>" required>
            <label for="email">E-mail do convidado</label>
            <input id="email" type="email" name="email" value="<?= htmlspecialchars($divulgacao['email']) ?>" required>
            <label for="data_evento">Data</label>
            <input id="data_evento" type="date" name="data_evento" value="<?= htmlspecialchars($divulgacao['data_evento']) ?>" required>
            <label for="local_evento">Local</label>
            <input id="local_evento" name="local_evento" value="<?= htmlspecialchars($divulgacao['local_evento']) ?>" required>
            <button type="submit">Salvar alterações</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
