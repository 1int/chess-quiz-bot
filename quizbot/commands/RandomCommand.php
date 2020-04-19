<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Entities\ServerResponse;

use QuizBot\QuizBot;
use QuizBot\QuizBotCommand;
use QuizBot\QuizBotRequest;
use RockstarsChess\PuzzleRepository;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class RandomCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = QuizBot::RANDOM_PUZZLE;

    /**
     * @var string
     */
    protected $description = 'Solve random puzzle (no rating)';

    /**
     * @var string
     */
    protected $usage = '/random';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute()
    {
        $chat_id = $this->getMessage()->getChat()->getId();
        $user_id = $this->getMessage()->getFrom()->getId();

        $puzzle = (new PuzzleRepository())->random();

        $conversation = new Conversation($user_id, $chat_id, QuizBot::RANDOM_PUZZLE);

        if( $puzzle && $puzzle->id ) {
            $conversation->notes = $puzzle->id;
            $conversation->update();

            return QuizBotRequest::sendPuzzle($puzzle, $chat_id);
        }
        else {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'Failed to get today\'s lichess puzzle'
            ]);
        }
    }


    /**
     * @return string
     */
    public static function postscriptMessage()
    {
        $values = [
            'Wanna go', 'Go', 'Let\'s try', 'Wanna solve', 'Care for', 'Ready for'
        ];

        return $values[array_rand($values)] . ' another /random?';
    }
}
