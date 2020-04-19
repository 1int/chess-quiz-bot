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

    public function __construct(Telegram $telegram, Update $update = null)
    {
        parent::__construct($telegram, $update);
        $this->quizBot = $telegram;
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
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => $text
        ]);
    }
}