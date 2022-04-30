<?php

namespace RockstarsChess;
use PDO;

class PlayerRepository
{
    protected $pdo;

    public function __construct()
    {
        $credentials = [
            'host'     => getenv('MYSQL_HOST'),
            'user'     => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASSWORD'),
            'database' => getenv('MYSQL_DB'),
        ];

        $dsn = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];

        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];
        $this->pdo = new PDO($dsn, $credentials['user'], $credentials['password'], $options);
    }


    /**
     * @return array
     */
    public function top($limit = 10)
    {
        $sql = "SELECT quiz_score.user_id, quiz_score.elo, user.first_name, user.last_name, user.username
                FROM quiz_score
                LEFT JOIN user on quiz_score.user_id = user.id
                order by elo desc 
                limit $limit";

        $sql = $this->pdo->prepare($sql);
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}