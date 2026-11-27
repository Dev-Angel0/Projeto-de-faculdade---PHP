<?php
// Centraliza os dados e os métodos de conexão com o banco MySQL.
class Database
{
    // Servidor usado pela conexão.
    private string $host = 'localhost';
    // Banco de dados utilizado pelo projeto.
    private string $dbName = 'plataforma_livros';
    // Usuário de acesso ao banco.
    private string $user = 'root';
    // Senha carregada de uma variável de ambiente por segurança.
    private string $password;

    // Obtém a senha antes de permitir uma conexão.
    public function __construct()
    {
        // Lê a senha da variável de ambiente DB_PASSWORD.
        $password = getenv('DB_PASSWORD');
        // Interrompe a inicialização quando a senha não foi configurada.
        if ($password === false) {
            throw new RuntimeException('A variável de ambiente DB_PASSWORD não está definida.');
        }
        // Guarda a senha para uso posterior.
        $this->password = $password;
    }

    // Abre e configura uma conexão PDO.
    public function conectar(): PDO
    {
        // Monta a sequência de conexão com banco, usuário e codificação UTF-8.
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
        // Retorna a conexão configurada para lançar exceções em falhas.
        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    // Converte uma falha técnica em uma mensagem adequada ao usuário.
    public static function mensagemErro(PDOException $erro, string $operacao): string
    {
        // Registra os detalhes técnicos apenas no log do servidor.
        error_log('Falha ao ' . $operacao . ': ' . $erro->getMessage());
        // Indica ao navegador que a operação falhou internamente.
        http_response_code(500);
        // Retorna uma orientação segura e objetiva.
        return 'Não foi possível ' . $operacao . '. Verifique os dados e a conexão com o banco, e tente novamente.';
    }
}
