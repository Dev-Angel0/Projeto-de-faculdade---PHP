<?php
// Lista as publicações com seus autores, tipos e divulgações.
// Importa conexão, configuração e representação de livro.
require_once 'config/database.php';
require_once 'classes/Livro.php';

$livros = [];
$erro = '';
$pdo = null;
$mensagem = $_GET['mensagem'] ?? '';
$tipoMensagem = $_GET['tipo'] ?? 'sucesso';
// Protege o carregamento da página contra falhas da conexão ou da consulta.
// Consulta as publicações dentro de uma tentativa protegida.
try {
    $pdo = (new Database())->conectar();
    // Recupera publicações e todos os dados relacionados para montar a tabela.
    $livros = $pdo->query(
        "SELECT p.*, t.nome AS tipo, d.data_evento, d.local_evento,
                c.nome AS convidado,
                (SELECT GROUP_CONCAT(CONCAT(a.nome, ' (', pa.funcao, ')') ORDER BY a.nome SEPARATOR ', ')
                 FROM publicacao_autor pa
                 JOIN autores a ON a.id_autor = pa.id_autor
                 WHERE pa.id_publicacao = p.id_publicacao) AS autores
         FROM publicacao p
         JOIN tipo_publicacao t ON t.id_tipo_publicacao = p.id_tipo_publicacao
         LEFT JOIN divulgacao d ON d.id_divulgacao = p.id_divulgacao
         LEFT JOIN convidados c ON c.id_convidado = d.id_convidado
         ORDER BY p.id_publicacao DESC"
    )->fetchAll();
// Registra uma falha segura na interface caso o banco não responda.
} catch (PDOException $exception) {
    $erro = Database::mensagemErro($exception, 'carregar as publicações');
}
// Usa a quantidade obtida para mostrar o total na interface.
$totalLivros = count($livros);
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Plataforma de Livros</title><link rel="stylesheet" href="assets/style.css"></head>
<body><header><h1>Plataforma de Lançamento de Livros</h1><nav><a href="frontend/index.html">Página inicial</a><a href="index.php">Publicações</a><a href="autores.php">Autores</a><a href="livro_form.php">Cadastrar publicação</a><a href="divulgacoes.php">Divulgações</a><a href="recursos.php">Recursos PHP</a></nav></header>
<main>
<?php if ($mensagem): ?><p class="mensagem <?= $tipoMensagem === 'erro' ? 'erro' : '' ?>"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="mensagem erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
<section class="card"><h2>Livros cadastrados: <?= $totalLivros ?></h2><p>Divulgação simples de livros acadêmicos e científicos.</p></section>
<section class="card"><label for="buscaLivro">Buscar livro</label><input id="buscaLivro" placeholder="Digite título, autor ou tipo"></section>
<table id="tabelaLivros"><thead><tr><th>Título</th><th>Autores e funções</th><th>Tipo</th><th>Publicação</th><th>Evento de divulgação</th><th>Ações</th></tr></thead><tbody>
<?php foreach ($livros as $dados): $livro = new Livro($dados['titulo'], $dados['data_publicacao']); ?>
<tr><td><?= htmlspecialchars($livro->descricao()) ?></td><td><?= htmlspecialchars($dados['autores'] ?? 'Sem autor') ?></td><td><?= htmlspecialchars($dados['tipo']) ?></td><td><?= date('d/m/Y', strtotime($dados['data_publicacao'])) ?></td><td><?php if ($dados['data_evento']): ?><?= date('d/m/Y', strtotime($dados['data_evento'])) ?> — <?= htmlspecialchars($dados['local_evento']) ?><br>Convidado: <?= htmlspecialchars($dados['convidado']) ?><?php else: ?>Sem evento agendado<?php endif; ?></td><td><a class="button" href="livro_form.php?id=<?= $dados['id_publicacao'] ?>">Editar</a> <a class="button danger" href="livro_excluir.php?id=<?= $dados['id_publicacao'] ?>" onclick="return confirm('Excluir esta publicação<?= $dados['id_divulgacao'] ? ' e sua divulgação' : '' ?>?')">Excluir</a></td></tr>
<?php endforeach; ?>
</tbody></table></main><script src="assets/app.js"></script></body></html>
