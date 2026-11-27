const campoBusca = document.querySelector('#buscaAcoes');
const cartoes = Array.from(document.querySelectorAll('.cartao-acao'));
const resultadoBusca = document.querySelector('#resultadoBusca');

campoBusca.addEventListener('input', function () {
    const termo = campoBusca.value.trim().toLocaleLowerCase('pt-BR');
    let quantidadeVisivel = 0;

    cartoes.forEach(function (cartao) {
        const conteudo = (cartao.textContent + ' ' + cartao.dataset.termos)
            .toLocaleLowerCase('pt-BR');
        const corresponde = conteudo.includes(termo);

        cartao.hidden = !corresponde;
        if (corresponde) {
            quantidadeVisivel++;
        }
    });

    resultadoBusca.textContent = termo && quantidadeVisivel === 0
        ? 'Nenhuma opção encontrada.'
        : '';
});
