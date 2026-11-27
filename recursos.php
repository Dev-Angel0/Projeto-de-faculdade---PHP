<?php
// Demonstra recursos básicos de PHP por meio de uma página informativa.
// Monta uma frase que recebe nome e quantidade como parâmetros.
function montarMensagem(string $nome, int $quantidade): string
{
    // Une os valores e informa quantos livros estão cadastrados.
    return $nome . ' possui ' . $quantidade . ' livro(s) cadastrado(s).';
}

// Define os dados utilizados pelas estruturas de controle.
$areas = ['Computação', 'Saúde', 'Educação'];
// Soma valores por meio de um operador aritmético.
$soma = 2 + 3;
// Incrementa o resultado usando atribuição composta.
$soma += 1;
// Inicializa o contador que contabiliza áreas prioritárias.
$contador = 0;
// Guarda uma linha de texto para cada área percorrida.
$linhas = [];
// Percorre o array por índice com for.
for ($i = 0; $i < count($areas); $i++) {
    $linhas[] = 'Área ' . ($i + 1) . ': ' . $areas[$i];
}
// Percorre cada área diretamente com foreach.
foreach ($areas as $area) {
    // Conta somente áreas que atendem à condição lógica.
    if ($area === 'Computação' || $area === 'Saúde') {
        $contador++;
    }
}
// Inicializa uma posição usada pelo while.
$posicao = 0;
// Avança até percorrer todas as posições do array.
while ($posicao < count($areas)) {
    $posicao++;
}
// Escolhe o status pela quantidade usando operador ternário.
$status = $contador >= 2 ? 'Áreas em destaque' : 'Outras áreas';
// Escolhe uma situação diferente conforme o valor do contador.
switch ($contador) {
    case 2: $situacao = 'Duas áreas prioritárias'; break;
    default: $situacao = 'Quantidade diferente de duas';
}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Recursos PHP</title><link rel="stylesheet" href="assets/style.css"></head><body><header><h1>Recursos de PHP usados</h1><nav><a href="index.php">Início</a></nav></header><main><section class="card"><h2>Demonstração simples</h2><p><?= montarMensagem('A plataforma', $soma) ?></p><p><?= $status ?> - <?= $situacao ?></p><ul><?php foreach ($linhas as $linha): ?><li><?= $linha ?></li><?php endforeach; ?></ul><p>O contador percorreu <?= $posicao ?> posições do array.</p></section></main></body></html>
