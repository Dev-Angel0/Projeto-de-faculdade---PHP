<?php
// O evento agora é cadastrado junto com a publicação.
header('Location: livro_form.php?mensagem=' . urlencode('A divulgação é opcional e pode ser preenchida junto com os dados do livro.'));
exit;
