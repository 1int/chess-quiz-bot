<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use QuizBot\QuizBot;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class PauseCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'pause';

    /**
     * @var string
     */
    protected $description = 'Pause the quiz';

    /**
     * @var string
     */
    protected $usage = '/pause';

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
        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => 'Ok, quiz is stopped. Hit /quiz to continue, /random for a puzzle or /help for all commands.',
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ];

        $user_id = $this->getMessage()->getFrom()->getId();
        $conversation = new Conversation($user_id, $user_id, QuizBot::QUIZ_PUZZLE);
        if( $conversation->exists() ) {
            $conversation->stop();
        }
        return Request::sendMessage($data);
    }
}
