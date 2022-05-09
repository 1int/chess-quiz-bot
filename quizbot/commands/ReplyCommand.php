<?php

namespace QuizBot\Commands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Request;

/**
 * Reply Command
 * Quick-made tool to reply to users' feedback sent with /feedback command
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
    public function execute(): ServerResponse
    {
        $from = $this->getMessage()->getFrom()->getId();

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
