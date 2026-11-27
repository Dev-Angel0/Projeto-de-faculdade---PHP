<?php
// Mantém compatibilidade e encaminha o formulário antigo para o novo fluxo integrado.
// A URL indica que a divulgação pode ser preenchida junto com a publicação.
header('Location: livro_form.php?mensagem=' . urlencode('A divulgação é opcional e pode ser preenchida junto com os dados do livro.'));
// Impede a execução de qualquer resposta adicional após o redirecionamento.
exit;
