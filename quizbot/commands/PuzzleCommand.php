<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Entities\ServerResponse;

use Longman\TelegramBot\TelegramLog;
use QuizBot\QuizBot;
use QuizBot\QuizBotCommand;
use QuizBot\QuizBotRequest;
use RockstarsChess\PuzzleRepository;


/**
* This one is for groups and channels.
 * Creats a poll.
 */
class PuzzleCommand extends QuizBotCommand
{
    protected $name = 'puzzle';
    protected $description = 'Sends a puzzle to group chat or a channel';
    protected $usage = '/puzzle';
    protected $version = '1.0.0';
    protected $private_only = false;

    /**
     * @return ServerResponse
     */
    public function execute($chat_id = null)
    {
        if(!$chat_id) {
            $chat_id = $this->getMessage()->getChat()->getId();
            $isChannel = false;
        }
        else {
            $isChannel = true;
        }
        $puzzle = (new PuzzleRepository())->random();
        return QuizBotRequest::sendPoll($puzzle, $chat_id, $isChannel);
    }
}
