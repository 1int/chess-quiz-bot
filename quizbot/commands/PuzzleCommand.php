<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Entities\ServerResponse;
use QuizBot\QuizBotCommand;
use QuizBot\QuizBotRequest;
use RockstarsChess\PuzzleRepository;
use Longman\TelegramBot\Request;


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
            if($this->getMessage()->getChat()->isPrivateChat()) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "This command is reserved for channels and groups.\nUse /quiz to play alone."
                ]);
            }
        }
        else {
            $isChannel = true;
        }
        $puzzle = (new PuzzleRepository())->random();
        return QuizBotRequest::sendPoll($puzzle, $chat_id, $isChannel);
    }
}
