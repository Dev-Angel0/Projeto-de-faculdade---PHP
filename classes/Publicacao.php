<?php
// Representa as informações comuns a todas as publicações.
class Publicacao
{
    // Armazena o título protegido contra alterações externas diretas.
    protected string $titulo;

    // Recebe o título no momento da criação do objeto.
    public function __construct(string $titulo)
    {
        // Guarda o título no atributo da classe.
        $this->titulo = $titulo;
    }

    // Permite consultar o título sem alterar o atributo diretamente.
    public function getTitulo(): string
    {
        // Retorna o título armazenado.
        return $this->titulo;
    }
}
