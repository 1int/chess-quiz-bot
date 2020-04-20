<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Request;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class ReplyCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'reply';

    /**
     * @var string
     */
    protected $description = 'Reply to last feedback';

    /**
     * @var string
     */
    protected $usage = '/reply';

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
        $from = $this->getMessage()->getFrom();

        if( $from != getenv('ADMIN') && $from != getenv('SECOND_ADMIN') ) {
            return Request::emptyResponse();
        }

        $pdo = DB::getPdo();
        $sql = "select chat_id from conversation where command='feedback' ORDER BY created_at DESC LIMIT 1";
        $chat_id = $pdo->query($sql)->fetchAll()[0]['chat_id'];

        $text = $this->getMessage()->getText(true);

        return Request::sendMessage([
           'chat_id' => $chat_id,
            'text' => $text
        ]);
    }
}
