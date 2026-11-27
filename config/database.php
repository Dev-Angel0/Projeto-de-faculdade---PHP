<?php
// Configura a conexão PDO com o banco de dados MySQL.
// Altere somente estes dados caso o seu MySQL use outra senha.
class Database
{
    private string $host = 'localhost';
    private string $dbName = 'plataforma_livros';
    private string $user = 'root';
    private string $password;

    public function __construct()
    {
        $password = getenv('DB_PASSWORD');
        if ($password === false) {
            throw new RuntimeException('A variável de ambiente DB_PASSWORD não está definida.');
        }
        $this->password = $password;
    }

    public function conectar(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public static function mensagemErro(PDOException $erro, string $operacao): string
    {
        error_log('Falha ao ' . $operacao . ': ' . $erro->getMessage());
        http_response_code(500);
        return 'Não foi possível ' . $operacao . '. Verifique os dados e a conexão com o banco, e tente novamente.';
    }
}
