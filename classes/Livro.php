<?php
// Importa a classe base que representa uma publicação.
require_once __DIR__ . '/Publicacao.php';

// Especializa Publicacao com a data de lançamento do livro.
class Livro extends Publicacao
{
    // Armazena a data específica da publicação do livro.
    private string $dataPublicacao;

    // Recebe título e data ao criar um livro.
    public function __construct(string $titulo, string $dataPublicacao)
    {
        // Inicializa o título pela classe pai.
        parent::__construct($titulo);
        // Guarda a data própria da classe Livro.
        $this->dataPublicacao = $dataPublicacao;
    }

    // Monta uma descrição pronta para exibição na página.
    public function descricao(): string
    {
        // Converte a data armazenada para o formato brasileiro.
        $dataFormatada = date('d/m/Y', strtotime($this->dataPublicacao));
        // Une título e data em uma única descrição.
        return $this->titulo . ' (' . $dataFormatada . ')';
    }
}
