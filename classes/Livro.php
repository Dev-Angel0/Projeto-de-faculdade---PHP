<?php
require_once __DIR__ . '/Publicacao.php';

// Herança: Livro é uma Publicação.
class Livro extends Publicacao
{
    private string $dataPublicacao;

    public function __construct(string $titulo, string $dataPublicacao)
    {
        parent::__construct($titulo);
        $this->dataPublicacao = $dataPublicacao;
    }

    public function descricao(): string
    {
        $dataFormatada = date('d/m/Y', strtotime($this->dataPublicacao)); // Formato brasileiro.
        return $this->titulo . ' (' . $dataFormatada . ')'; // Operador de string.
    }
}
