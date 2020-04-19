<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Quizbot\QuizBotCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class FeedbackCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = 'feedback';

    /**
     * @var string
     */
    protected $description = 'Send your feedback about the bot';

    /**
     * @var string
     */
    protected $usage = '/feedback <text>';

    /**
     * @var string
     */
    protected $version = '1.0';

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
        $user = $this->getMessage()->getFrom();
        $from = $user->getFirstName() . ' ' . $user->getLastName() . ' (@' . $user->getUsername() . ')';
        $text = $this->getMessage()->getText(true);

        $data = [
            'text' => "ChessQuizBot feedback from $from\n-----------------------\n\n" . $text,
            'chat_id' => getenv('ADMIN'),
            'parse_mode' => 'markdown'
        ];
        return Request::sendMessage($data);
    }
}
