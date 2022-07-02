<?php

namespace QuizBot\Commands;

use Longman\TelegramBot\Entities\ServerResponse;
use QuizBot\QuizBotCommand;
use QuizBot\QuizBotRequest;
use RockstarsChess\PuzzleRepository;
use Longman\TelegramBot\Request;


/**
* Puzzle Command.
* This one is for groups and channels.
* Creats a poll instead of setting user keyboard.
*
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
    public function execute(): ServerResponse
    {
        if(is_null($this->chat_id)) {
            $this->chat_id = $this->getMessage()->getChat()->getId();
            $isChannel = false;
            if($this->getMessage()->getChat()->isPrivateChat()) {
                return Request::sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => "This command is reserved for channels and groups.\nUse /quiz to play alone."
                ]);
            }
        }
        else {
            $isChannel = true;
        }
        $puzzle = (new PuzzleRepository())->random();
        return QuizBotRequest::sendPoll($puzzle, $this->chat_id, $isChannel);
    }
}
