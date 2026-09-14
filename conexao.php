<?php
declare(strict_types=1);

function conectarBD(): PDO
{
    $host = '127.0.0.1';
    $dbname = 'mypocket';
    $usuario = 'root';
    $senha = '';

    try {
        return new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $usuario,
            $senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
    }
}
