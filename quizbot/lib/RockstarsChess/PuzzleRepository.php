<?php

namespace RockstarsChess;
use PDO;

class PuzzleRepository
{
    protected $pdo;

    public function __construct()
    {
        $credentials = [
            'host'     => getenv('MYSQL_HOST'),
            'user'     => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASSWORD'),
            'database' => getenv('MYSQL_ROCKSTARS_DB'),
        ];

        $dsn = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];

        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];
        $this->pdo = new PDO($dsn, $credentials['user'], $credentials['password'], $options);
    }


    /**
     * @return Puzzle
     */
    public function random()
    {
        $sql = "SELECT * FROM tactics_positions WHERE `options` IS NOT NULL ORDER BY RAND() LIMIT 1";
        return $this->pdo->query($sql)->fetchObject(Puzzle::class);
    }

    /**
     * @param string $id
     * @return Puzzle
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM tactics_positions WHERE id=:id";
        $sql = $this->pdo->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();
        return $sql->fetchObject(Puzzle::class);
    }

    public function updatePuzzleStats(Puzzle $puzzle)
    {
        $sql = "UPDATE tactics_positions set wrong_answers=:w, correct_answers=:c, elo=:elo WHERE id=:id";
        $sql = $this->pdo->prepare($sql);
        $sql->bindValue(':id', $puzzle->id);
        $sql->bindValue(':c', $puzzle->correct_answers);
        $sql->bindValue(':w', $puzzle->wrong_answers);
        $sql->bindValue(':elo', $puzzle->elo);
        $sql->execute();
    }


}