// JavaScript usado no projeto: busca na tabela e validação simples de formulários.
document.addEventListener('DOMContentLoaded', function () {
    const campoBusca = document.querySelector('#buscaLivro'); // Campo de busca da página inicial.
    const linhas = document.querySelectorAll('#tabelaLivros tbody tr');

    if (campoBusca) {
        campoBusca.addEventListener('input', function () {
            const termo = campoBusca.value.toLowerCase();
            linhas.forEach(function (linha) { // Mostra apenas as linhas que combinam com a busca.
                linha.style.display = linha.textContent.toLowerCase().includes(termo) ? '' : 'none';
            });
        });
    }

    const listaAutorias = document.querySelector('#autorias');
    const botaoAdicionarAutoria = document.querySelector('#adicionarAutoria');

    if (listaAutorias && botaoAdicionarAutoria) {
        let proximoIndiceAutoria = listaAutorias.children.length;

        function atualizarBotoesRemover() {
            const botoes = listaAutorias.querySelectorAll('.remover-autoria');
            botoes.forEach(function (botao) {
                botao.disabled = botoes.length === 1;
            });
        }

        listaAutorias.addEventListener('click', function (evento) {
            if (evento.target.matches('.remover-autoria') && listaAutorias.children.length > 1) {
                evento.target.closest('.linha-autoria').remove();
                atualizarBotoesRemover();
            }
        });

        botaoAdicionarAutoria.addEventListener('click', function () {
            const linha = listaAutorias.firstElementChild.cloneNode(true);
            const indice = proximoIndiceAutoria;
            proximoIndiceAutoria++;
            linha.querySelectorAll('select').forEach(function (select) {
                select.name = select.name.replace(/autores\[\d+\]/, 'autores[' + indice + ']');
                select.value = select.name.endsWith('[funcao]') ? 'Autor' : '';
            });
            listaAutorias.appendChild(linha);
            atualizarBotoesRemover();
        });

        atualizarBotoesRemover();
    }

    document.querySelectorAll('form').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!formulario.checkValidity()) {
                evento.preventDefault();
                alert('Preencha os campos obrigatórios antes de salvar.');
            }
        });
    });
});
