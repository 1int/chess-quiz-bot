<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Request;
use Quizbot\QuizBotCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start the bot';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.1.0';


    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute()
    {
        $text    = 'Hi there! ' . PHP_EOL . PHP_EOL .
            '- hit /quiz to start a challenge' . PHP_EOL .
            '- /random for one puzzle' . PHP_EOL .
            PHP_EOL .
            '👥💬 This bot can be used in channels and groups.' . PHP_EOL .
            'Use /puzzle command in a chat or a group to play with friends.';

        return $this->replyWithText($text);
    }
}
