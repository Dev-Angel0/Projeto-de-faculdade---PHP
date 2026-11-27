# Plataforma de Livros

Projeto acadêmico para cadastrar livros, seus autores e, opcionalmente, um evento de divulgação. O sistema usa HTML, CSS e JavaScript na interface, PHP para processar as operações e MySQL para armazenar os dados.

## Como funciona

- Cadastre um ou mais autores antes de cadastrar um livro.
- Ao cadastrar ou editar um livro, associe seus autores e suas funções (autor, coautor ou revisor).
- O evento de divulgação é opcional: pode ser incluído junto com o livro ou adicionado depois pela edição.
- Excluir um evento mantém o livro cadastrado. Livros, autores e divulgações podem ser consultados e gerenciados nas páginas do sistema.

## Configuração

1. Instale PHP com as extensões PDO e `pdo_mysql`, além de um servidor MySQL.
2. Em `config/database.php`, configure o host, o nome do banco e o usuário MySQL (`$host`, `$dbName` e `$user`).
3. Configure a variável de ambiente `DB_PASSWORD` com a senha desse usuário. O PHP precisa receber essa variável no processo que executa o servidor. Não coloque a senha no repositório.
4. Execute `database.sql` no MySQL para criar o banco e suas tabelas.

> **Atenção:** `database.sql` apaga e recria o banco `plataforma_livros`. Executá-lo novamente remove os dados existentes. O script precisa ser executado manualmente; o projeto não o executa ao iniciar.

## Executar localmente

No terminal, a partir da pasta do projeto, inicie o servidor PHP:

```powershell
php -S localhost:8000
```

Abra `http://localhost:8000/frontend/` no navegador. O servidor PHP deve estar configurado com `pdo_mysql` e com a variável `DB_PASSWORD` disponível.