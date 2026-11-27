<?php
// Cadastro e listagem de autores.
require_once 'config/database.php';
$erro = '';
$autores = [];
$nomeFormulario = '';
$emailFormulario = '';
$mensagem = $_GET['mensagem'] ?? '';
$tipoMensagem = $_GET['tipo'] ?? 'sucesso';

try {
    $pdo = (new Database())->conectar();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nomeFormulario = is_string($_POST['nome'] ?? null) ? trim($_POST['nome']) : '';
        $emailFormulario = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        if ($nomeFormulario === '' || !filter_var($emailFormulario, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um nome e um e-mail válido para o autor.';
        } else {
            $pdo->prepare('INSERT INTO autores (nome, email) VALUES (?, ?)')->execute([$nomeFormulario, $emailFormulario]);
            header('Location: autores.php?mensagem=' . urlencode('Autor cadastrado'));
            exit;
        }
    }
    $autores = $pdo->query(
        'SELECT a.*,
                (SELECT COUNT(*) FROM publicacao_autor pa WHERE pa.id_autor = a.id_autor) AS total_publicacoes
         FROM autores a
         ORDER BY a.nome'
    )->fetchAll();
} catch (PDOException $exception) {
    $erro = Database::mensagemErro($exception, 'salvar ou carregar os autores');
}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Autores</title><link rel="stylesheet" href="assets/style.css"></head><body><header><h1>Autores</h1><nav><a href="index.php">Início</a></nav></header><main>
<?php if ($mensagem !== ''): ?><p class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : '' ?>"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="mensagem erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
<form method="post"><h2>Novo autor</h2><label>Nome</label><input name="nome" value="<?= htmlspecialchars($nomeFormulario) ?>" required><label>E-mail</label><input type="email" name="email" value="<?= htmlspecialchars($emailFormulario) ?>" required><button>Cadastrar</button></form>
<table><tr><th>Nome</th><th>E-mail</th><th>Publicações</th><th>Ações</th></tr><?php foreach ($autores as $autor): ?><tr><td><?= htmlspecialchars($autor['nome']) ?></td><td><?= htmlspecialchars($autor['email']) ?></td><td><?= (int)$autor['total_publicacoes'] ?></td><td><a class="button" href="autor_form.php?id=<?= $autor['id_autor'] ?>">Editar</a> <a class="button danger" href="autor_excluir.php?id=<?= $autor['id_autor'] ?>" onclick="return confirm('Excluir este autor?')">Excluir</a></td></tr><?php endforeach; ?></table>
</main></body></html>
