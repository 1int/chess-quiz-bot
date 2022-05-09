<?php

namespace QuizBot\Commands;

use Quizbot\QuizBotCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use RockstarsChess\PlayerRepository;

/**
 * Class TopCommand
 * @package Longman\TelegramBot\Commands\SystemCommands
 *
 * Shows top N users, usually N=10
 */
class TopCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = 'top';

    /**
     * @var string
     */
    protected $description = 'Top players';

    /**
     * @var string
     */
    protected $usage = '/top';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    const LIMIT = 10;


    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $repo = new PlayerRepository();
        $players = $repo->top(self::LIMIT);

        $header = sprintf("*Top %d players* 🏆\n\n", self::LIMIT);
        $playersText = '';

        foreach($players as $player) {
            $q = '';
            $hasNickname = isset($player['username']) && $player['username'] && strpos($player['username'], '_') === false;
            $nickname =  $hasNickname ? ' (@'. $player['username'] . ')':'';
            $firstName = $player['first_name'] ? $q . $player['first_name'] . $q : '';
            $lastName = $player['last_name'] ? ' ' . $q . $player['last_name']. $q : '';
            $emo = '';

            $playersText .= '*' . $player['elo'] . '*' . ' ' . $emo . $firstName . $lastName . $nickname . ' ' . "\n";
        }

        return $this->replyWithMarkdown($header . $playersText);
    }
}
