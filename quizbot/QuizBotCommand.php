<?php

namespace QuizBot;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Class QuizBotCommand
 * @package QuizBot
 *
 * This is a wrapper for all ChessQuizBot commands.
 * Not really used as of now, basically just provides direct access to the bot instance via $command->quizBot
 */
abstract class QuizBotCommand extends UserCommand
{
    /** @var QuizBot */
    public $quizBot;

    /** @var int|null predefined chat it to reply to */
    protected $chat_id = null;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        parent::__construct($telegram, $update);
        $this->quizBot = $telegram;
    }

    public function setChatId($chat_id): QuizBotCommand
    {
        $this->chat_id = $chat_id;
        return $this;
    }

    public function getChatId(): int
    {
        if(is_null($this->chat_id)) {
            $msg = $this->getMessage() ? $this->getMessage() : $this->getChannelPost();
            return $msg->getChat()->getId();
        }
        else {
            return $this->chat_id;
        }
    }

    /**
     * A shortcut to just send back some text to whoever the message came from.
     *
     * @param $text
     * @return ServerResponse
     * @throws TelegramException
     */
    public function replyWithText($text)
    {
        return Request::sendMessage([
            'chat_id' => $this->getChatId(),
            'text' => $text
        ]);
    }

    /**
     * @param string text
     * @return ServerResponse
     * @throws TelegramException
     */
    public function replyWithMarkdown($text)
    {
       $chat_id = $this->getChatId();

        $data = [
            'chat_id' => $chat_id,
            'parse_mode' => 'markdown',
            'text' => $text
        ];

        return Request::sendMessage($data);
    }
}