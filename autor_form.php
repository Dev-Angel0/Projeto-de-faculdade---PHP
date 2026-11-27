<?php
// Consulta e atualiza os dados cadastrais de um autor.
// Importa a configuração de conexão com o banco.
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
$erro = '';
$autor = ['nome' => '', 'email' => ''];

try {
    // Abre a conexão com o banco.
    $pdo = (new Database())->conectar();
    // Busca o autor pelo identificador recebido.
    $consulta = $pdo->prepare('SELECT * FROM autores WHERE id_autor = ?');
    $consulta->execute([$id]);
    // Recupera o registro encontrado.
    $autorEncontrado = $consulta->fetch();
    // Interrompe a edição se o autor não existir.
    if (!$autorEncontrado) {
        header('Location: autores.php?mensagem=' . urlencode('Autor não encontrado') . '&tipo=erro');
        exit;
    }
    // Mantém os dados no formulário.
    $autor = $autorEncontrado;

    // Processa as alterações enviadas pelo formulário.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
        $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        // Exibe erro e preserva os valores quando os dados são inválidos.
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um nome e um e-mail válido para o autor.';
            $autor['nome'] = $nome;
            $autor['email'] = $email;
        } else {
            // Atualiza o registro correspondente ao identificador.
            $pdo->prepare('UPDATE autores SET nome = ?, email = ? WHERE id_autor = ?')->execute([$nome, $email, $id]);
            header('Location: autores.php?mensagem=' . urlencode('Autor atualizado'));
            exit;
        }
    }
// Captura falhas ao buscar ou alterar o autor.
} catch (PDOException $exception) {
    $erro = Database::mensagemErro($exception, 'carregar ou atualizar o autor');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar autor</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header><h1>Editar autor</h1><nav><a href="autores.php">Autores</a></nav></header>
<main>
    <?php if ($erro !== ''): ?><p class="mensagem erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
    <form method="post">
        <label for="nome">Nome</label>
        <input id="nome" name="nome" value="<?= htmlspecialchars($autor['nome']) ?>" required>
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" value="<?= htmlspecialchars($autor['email']) ?>" required>
        <button type="submit">Salvar alterações</button>
    </form>
</main>
</body>
</html>
