<?php
// Cadastra ou edita uma publicação, seus autores e sua divulgação opcional.
// Carrega a configuração necessária para acessar o banco.
require_once 'config/database.php';

// Identifica edição quando um identificador é recebido pela URL.
$id = (int)($_GET['id'] ?? 0);
$erro = '';
$mensagem = $_GET['mensagem'] ?? '';
$autores = [];
$tipos = [];
$livro = [
    'titulo' => '',
    'resumo' => '',
    'data_publicacao' => date('Y-m-d'),
    'id_tipo_publicacao' => '',
    'id_divulgacao' => null,
    'id_convidado' => null,
    'data_evento' => '',
    'local_evento' => '',
    'convidado_nome' => '',
    'convidado_email' => ''
];
$autorias = [['id_autor' => '', 'funcao' => 'Autor']];
$funcoes = ['Autor', 'Coautor', 'Revisor'];
$autoriasEnviadas = null;

try {
    // Abre a conexão usada por consultas e alterações.
    $pdo = (new Database())->conectar();
    // Carrega autores disponíveis no formulário.
    $autores = $pdo->query('SELECT id_autor, nome FROM autores ORDER BY nome')->fetchAll();
    $tipos = $pdo->query('SELECT id_tipo_publicacao, nome FROM tipo_publicacao ORDER BY nome')->fetchAll();

    // Quando há identificador, carrega os dados existentes para edição.
    if ($id > 0) {
        // Prepara a consulta dos dados principais da publicação existente.
        $consulta = $pdo->prepare(
            'SELECT p.*, d.data_evento, d.local_evento,
                    c.id_convidado, c.nome AS convidado_nome, c.email AS convidado_email
             FROM publicacao p
             LEFT JOIN divulgacao d ON d.id_divulgacao = p.id_divulgacao
             LEFT JOIN convidados c ON c.id_convidado = d.id_convidado
             WHERE p.id_publicacao = ?'
        );
        $consulta->execute([$id]);
        // Carrega as relações entre publicação e autores.
        // Recupera a publicação e sua divulgação.
        $livroEncontrado = $consulta->fetch();
        // Cancela a edição quando a publicação não existe.
        if (!$livroEncontrado) {
            header('Location: index.php?mensagem=' . urlencode('Publicação não encontrada') . '&tipo=erro');
            exit;
        }
        // Guarda os dados existentes no formato usado pelo formulário.
        $livro = $livroEncontrado;
        $consulta = $pdo->prepare(
            'SELECT id_autor, funcao FROM publicacao_autor WHERE id_publicacao = ? ORDER BY id_autor'
        );
        $consulta->execute([$id]);
        $autorias = $consulta->fetchAll();
        if (!$autorias) {
            $autorias = [['id_autor' => '', 'funcao' => 'Autor']];
        }
    }

    // Processa os dados enviados pelo formulário de cadastro ou edição.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Normaliza os textos e campos principais da publicação.
        $livro['titulo'] = is_string($_POST['titulo'] ?? null) ? trim($_POST['titulo']) : '';
        $livro['resumo'] = is_string($_POST['resumo'] ?? null) ? trim($_POST['resumo']) : '';
        $livro['data_publicacao'] = is_string($_POST['data_publicacao'] ?? null) ? $_POST['data_publicacao'] : '';
        $livro['id_tipo_publicacao'] = is_scalar($_POST['id_tipo_publicacao'] ?? null) ? (string)$_POST['id_tipo_publicacao'] : '';
        $livro['data_evento'] = is_string($_POST['data_evento'] ?? null) ? $_POST['data_evento'] : '';
        $livro['local_evento'] = is_string($_POST['local_evento'] ?? null) ? trim($_POST['local_evento']) : '';
        $livro['convidado_nome'] = is_string($_POST['convidado_nome'] ?? null) ? trim($_POST['convidado_nome']) : '';
        $livro['convidado_email'] = is_string($_POST['convidado_email'] ?? null) ? trim($_POST['convidado_email']) : '';
        // Recebe a matriz de autores enviada pelo formulário.
        $autoriasEnviadas = $_POST['autores'] ?? [];
        $autorias = is_array($autoriasEnviadas)
            ? array_values(array_filter($autoriasEnviadas, 'is_array'))
            : [];
        if (!$autorias) {
            $autorias = [['id_autor' => '', 'funcao' => 'Autor']];
        }

        // Detecta se o usuário informou algum dado da divulgação opcional.
        $eventoInformado = $livro['data_evento'] !== ''
            || $livro['local_evento'] !== ''
            || $livro['convidado_nome'] !== ''
            || $livro['convidado_email'] !== '';
        // Valida a data principal da publicação.
        $dataPublicacao = DateTime::createFromFormat('!Y-m-d', $livro['data_publicacao']);
        $dataPublicacaoErros = DateTime::getLastErrors();
        $dataPublicacaoValida = $dataPublicacao && (!$dataPublicacaoErros || ($dataPublicacaoErros['warning_count'] === 0 && $dataPublicacaoErros['error_count'] === 0));
        $dataEventoValida = true;
        // Valida a data do evento somente quando ele foi informado.
        if ($eventoInformado) {
            $dataEvento = DateTime::createFromFormat('!Y-m-d', $livro['data_evento']);
            $dataEventoErros = DateTime::getLastErrors();
            $dataEventoValida = $dataEvento && (!$dataEventoErros || ($dataEventoErros['warning_count'] === 0 && $dataEventoErros['error_count'] === 0));
        }

        $idsAutores = [];
        $autoriasValidas = [];
        // Analisa cada autor enviado e aceita somente identificador e função válidos.
        foreach ($autorias as $autoria) {
            if (!is_array($autoria)) {
                continue;
            }
            $idAutorEnviado = $autoria['id_autor'] ?? null;
            $idAutor = is_scalar($idAutorEnviado)
                ? filter_var((string)$idAutorEnviado, FILTER_VALIDATE_INT)
                : false;
            $funcao = is_string($autoria['funcao'] ?? null) ? trim($autoria['funcao']) : '';
            if ($idAutor && in_array($funcao, $funcoes, true)) {
                $idsAutores[] = $idAutor;
                $autoriasValidas[] = ['id_autor' => $idAutor, 'funcao' => $funcao];
            }
        }

        if ($livro['titulo'] === '' || $livro['resumo'] === '' || !$dataPublicacaoValida) {
            $erro = 'Preencha o título, o resumo e uma data de publicação válida.';
        } elseif ($eventoInformado && (!$dataEventoValida || $livro['local_evento'] === ''
            || $livro['convidado_nome'] === ''
            || !filter_var($livro['convidado_email'], FILTER_VALIDATE_EMAIL))) {
            $erro = 'Para cadastrar a divulgação, preencha todos os dados do evento e informe um e-mail válido.';
        } elseif (!$livro['id_tipo_publicacao'] || !$autoriasValidas) {
            $erro = 'Selecione um tipo de publicação e pelo menos um autor com uma função válida.';
        } elseif (count($idsAutores) !== count(array_unique($idsAutores))) {
            $erro = 'Não repita o mesmo autor na publicação.';
        } elseif (count($autoriasValidas) !== count($autorias)) {
            $erro = 'Cada autor precisa ter um ID e uma função válida.';
        } else {
            // Confere se o tipo e todos os autores existem no banco.
            $tipoExiste = $pdo->prepare('SELECT COUNT(*) FROM tipo_publicacao WHERE id_tipo_publicacao = ?');
            $tipoExiste->execute([$livro['id_tipo_publicacao']]);
            $autoresExistem = $pdo->prepare(
                'SELECT COUNT(*) FROM autores WHERE id_autor IN (' .
                implode(',', array_fill(0, count($idsAutores), '?')) . ')'
            );
            $autoresExistem->execute($idsAutores);

            if ((int)$tipoExiste->fetchColumn() !== 1 || (int)$autoresExistem->fetchColumn() !== count($idsAutores)) {
                $erro = 'O tipo de publicação ou um dos autores selecionados não existe.';
            } else {
                // Agrupa todas as alterações em uma única transação.
                $pdo->beginTransaction();
                // Distingue atualização de cadastro pelo identificador da publicação.
                if ($id > 0) {
                    $pdo->prepare(
                        'UPDATE publicacao SET titulo = ?, resumo = ?, data_publicacao = ?, id_tipo_publicacao = ?
                         WHERE id_publicacao = ?'
                    )->execute([
                        $livro['titulo'], $livro['resumo'], $livro['data_publicacao'],
                        $livro['id_tipo_publicacao'], $id
                    ]);
                    if ($eventoInformado && $livro['id_divulgacao']) {
                        // Atualiza evento e convidado já existentes.
                        $pdo->prepare(
                            'UPDATE convidados SET nome = ?, email = ? WHERE id_convidado = ?'
                        )->execute([$livro['convidado_nome'], $livro['convidado_email'], $livro['id_convidado']]);
                        $pdo->prepare(
                            'UPDATE divulgacao SET data_evento = ?, local_evento = ? WHERE id_divulgacao = ?'
                        )->execute([$livro['data_evento'], $livro['local_evento'], $livro['id_divulgacao']]);
                    } elseif ($eventoInformado) {
                        // Cria evento e convidado quando a publicação ainda não os possui.
                        $pdo->prepare('INSERT INTO convidados (nome, email) VALUES (?, ?)')->execute([
                            $livro['convidado_nome'], $livro['convidado_email']
                        ]);
                        $idConvidado = $pdo->lastInsertId();
                        $pdo->prepare(
                            'INSERT INTO divulgacao (data_evento, local_evento, id_convidado) VALUES (?, ?, ?)'
                        )->execute([$livro['data_evento'], $livro['local_evento'], $idConvidado]);
                        $idDivulgacao = $pdo->lastInsertId();
                        $pdo->prepare('UPDATE publicacao SET id_divulgacao = ? WHERE id_publicacao = ?')
                            ->execute([$idDivulgacao, $id]);
                    } elseif ($livro['id_divulgacao']) {
                        // Remove apenas a divulgação quando o usuário limpa todos os dados do evento.
                        $pdo->prepare('UPDATE publicacao SET id_divulgacao = NULL WHERE id_publicacao = ?')
                            ->execute([$id]);
                        $pdo->prepare('DELETE FROM divulgacao WHERE id_divulgacao = ?')
                            ->execute([$livro['id_divulgacao']]);
                        $pdo->prepare(
                            'DELETE FROM convidados
                             WHERE id_convidado = ?
                               AND NOT EXISTS (SELECT 1 FROM divulgacao WHERE id_convidado = ?)'
                        )->execute([$livro['id_convidado'], $livro['id_convidado']]);
                    }
                    $pdo->prepare('DELETE FROM publicacao_autor WHERE id_publicacao = ?')->execute([$id]);
                    // Define a mensagem final de acordo com o estado da divulgação.
                    $mensagem = $eventoInformado
                        ? 'Publicação e divulgação atualizadas'
                        : ($livro['id_divulgacao'] ? 'Publicação atualizada e divulgação removida' : 'Publicação atualizada');
                } else {
                    $idDivulgacao = null;
                    if ($eventoInformado) {
                        // Cadastra primeiro o convidado e depois o evento.
                        $pdo->prepare('INSERT INTO convidados (nome, email) VALUES (?, ?)')->execute([
                            $livro['convidado_nome'], $livro['convidado_email']
                        ]);
                        $idConvidado = $pdo->lastInsertId();
                        $pdo->prepare(
                            'INSERT INTO divulgacao (data_evento, local_evento, id_convidado) VALUES (?, ?, ?)'
                        )->execute([$livro['data_evento'], $livro['local_evento'], $idConvidado]);
                        $idDivulgacao = $pdo->lastInsertId();
                    }
                    // Insere a publicação principal e obtém seu novo identificador.
                    $pdo->prepare(
                        'INSERT INTO publicacao (titulo, resumo, data_publicacao, id_divulgacao, id_tipo_publicacao)
                         VALUES (?, ?, ?, ?, ?)'
                    )->execute([
                        $livro['titulo'], $livro['resumo'], $livro['data_publicacao'],
                        $idDivulgacao, $livro['id_tipo_publicacao']
                    ]);
                    $id = (int)$pdo->lastInsertId();
                    $mensagem = $eventoInformado
                        ? 'Publicação e divulgação cadastradas'
                        : 'Publicação cadastrada sem divulgação';
                }

                // Prepara o vínculo entre publicação e cada autor validado.
                $inserirAutoria = $pdo->prepare(
                    'INSERT INTO publicacao_autor (id_publicacao, id_autor, funcao) VALUES (?, ?, ?)'
                );
                foreach ($autoriasValidas as $autoria) {
                    $inserirAutoria->execute([$id, $autoria['id_autor'], $autoria['funcao']]);
                }
                // Confirma toda a operação somente depois dos vínculos serem criados.
                $pdo->commit();
                header('Location: index.php?mensagem=' . urlencode($mensagem));
                exit;
            }
        }
    }
// Captura falhas, desfaz alterações pendentes e informa o erro ao usuário.
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $erro = Database::mensagemErro($exception, 'salvar ou carregar a publicação e sua divulgação');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Editar publicação' : 'Cadastrar publicação' ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <h1><?= $id ? 'Editar publicação' : 'Cadastrar publicação' ?></h1>
    <nav><a href="index.php">Início</a><a href="frontend/index.html">Página inicial</a></nav>
</header>
<main>
    <?php if ($mensagem !== ''): ?><p class="mensagem"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
    <?php if ($erro !== ''): ?><p class="mensagem erro"><?= htmlspecialchars($erro) ?></p><?php endif; ?>
    <?php if (!$autores): ?>
        <section class="card">
            <h2>Cadastre um autor primeiro</h2>
            <p>É necessário ter ao menos um autor para cadastrar uma publicação.</p>
            <a class="button" href="autores.php">Cadastrar autor</a>
        </section>
    <?php elseif (!$tipos): ?>
        <section class="card"><p class="mensagem erro">Nenhum tipo de publicação está cadastrado no banco.</p></section>
    <?php else: ?>
        <form method="post">
            <h2>Dados do livro</h2>
            <label for="titulo">Título</label>
            <input id="titulo" name="titulo" maxlength="150" value="<?= htmlspecialchars($livro['titulo']) ?>" required>
            <label for="resumo">Resumo</label>
            <textarea id="resumo" name="resumo" required><?= htmlspecialchars($livro['resumo']) ?></textarea>
            <label for="data_publicacao">Data de publicação</label>
            <input id="data_publicacao" type="date" name="data_publicacao" value="<?= htmlspecialchars($livro['data_publicacao']) ?>" required>
            <label for="id_tipo_publicacao">Tipo de publicação</label>
            <select id="id_tipo_publicacao" name="id_tipo_publicacao" required>
                <option value="">Selecione</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= $tipo['id_tipo_publicacao'] ?>" <?= (string)$livro['id_tipo_publicacao'] === (string)$tipo['id_tipo_publicacao'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tipo['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <h2>Autores e funções</h2>
            <div id="autorias">
                <?php foreach ($autorias as $indice => $autoria): ?>
                    <div class="linha-autoria">
                        <label>Autor
                            <select name="autores[<?= $indice ?>][id_autor]" required>
                                <option value="">Selecione</option>
                                <?php foreach ($autores as $autor): ?>
                                    <option value="<?= $autor['id_autor'] ?>" <?= (string)($autoria['id_autor'] ?? '') === (string)$autor['id_autor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($autor['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Função
                            <select name="autores[<?= $indice ?>][funcao]" required>
                                <?php foreach ($funcoes as $funcao): ?>
                                    <option value="<?= htmlspecialchars($funcao) ?>" <?= ($autoria['funcao'] ?? 'Autor') === $funcao ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($funcao) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button type="button" class="button danger remover-autoria">Remover autor</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="adicionarAutoria">Adicionar outro autor</button>

            <h2>Evento de divulgação (opcional)</h2>
            <p>Deixe todos os campos vazios para salvar sem evento. Ao editar, limpar todos os campos remove a divulgação e mantém a publicação.</p>
            <label for="convidado_nome">Nome do convidado</label>
            <input id="convidado_nome" name="convidado_nome" value="<?= htmlspecialchars($livro['convidado_nome'] ?? '') ?>">
            <label for="convidado_email">E-mail do convidado</label>
            <input id="convidado_email" type="email" name="convidado_email" value="<?= htmlspecialchars($livro['convidado_email'] ?? '') ?>">
            <label for="data_evento">Data do evento</label>
            <input id="data_evento" type="date" name="data_evento" value="<?= htmlspecialchars($livro['data_evento'] ?? '') ?>">
            <label for="local_evento">Local do evento</label>
            <input id="local_evento" name="local_evento" maxlength="100" value="<?= htmlspecialchars($livro['local_evento'] ?? '') ?>">
            <button type="submit">Salvar publicação</button>
        </form>
    <?php endif; ?>
</main>
<script src="assets/app.js"></script>
</body>
</html>
