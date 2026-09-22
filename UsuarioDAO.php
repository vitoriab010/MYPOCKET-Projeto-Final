<?php
declare(strict_types=1);

require_once 'Usuario.php';

class UsuarioDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // um usuário padrão para poder fixar as carteiras a alguém.
    public function buscarOuCriarPadrao(): Usuario
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE email = :email");
        $stmt->execute(['email' => 'usuario@mypocket.com']);
        $linha = $stmt->fetch();

        if ($linha) {
            return new Usuario((int) $linha['id_usuario'], $linha['nome'], $linha['email'], $linha['senha']);
        }

        $senhaHash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)"
        );
        $stmt->execute([
            'nome' => 'Usuário Padrão',
            'email' => 'usuario@mypocket.com',
            'senha' => $senhaHash,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        return new Usuario($id, 'Usuário Padrão', 'usuario@mypocket.com', $senhaHash);
    }
}
