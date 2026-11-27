<?php
// Classe base usada para representar uma publicação.
class Publicacao
{
    protected string $titulo;

    public function __construct(string $titulo)
    {
        $this->titulo = $titulo;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }
}
