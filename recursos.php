<?php
// Pagina curta para evidenciar os recursos solicitados no enunciado.
function montarMensagem(string $nome, int $quantidade): string
{
    return $nome . ' possui ' . $quantidade . ' livro(s) cadastrado(s).';
}

$areas = ['Computação', 'Saúde', 'Educação']; // Array.
$soma = 2 + 3; // Operador aritmetico.
$soma += 1; // Atribuicao.
$contador = 0;
$linhas = [];
for ($i = 0; $i < count($areas); $i++) { // For, comparacao e incremento.
    $linhas[] = 'Área ' . ($i + 1) . ': ' . $areas[$i];
}
foreach ($areas as $area) { // Foreach.
    if ($area === 'Computação' || $area === 'Saúde') { // If/else e operador logico.
        $contador++;
    }
}
$posicao = 0;
while ($posicao < count($areas)) { // While.
    $posicao++;
}
$status = $contador >= 2 ? 'Áreas em destaque' : 'Outras áreas'; // Ternario.
switch ($contador) { // Switch.
    case 2: $situacao = 'Duas áreas prioritárias'; break;
    default: $situacao = 'Quantidade diferente de duas';
}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Recursos PHP</title><link rel="stylesheet" href="assets/style.css"></head><body><header><h1>Recursos de PHP usados</h1><nav><a href="index.php">Início</a></nav></header><main><section class="card"><h2>Demonstração simples</h2><p><?= montarMensagem('A plataforma', $soma) ?></p><p><?= $status ?> - <?= $situacao ?></p><ul><?php foreach ($linhas as $linha): ?><li><?= $linha ?></li><?php endforeach; ?></ul><p>O contador percorreu <?= $posicao ?> posições do array.</p></section></main></body></html>
