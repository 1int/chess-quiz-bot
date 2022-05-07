<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\WebhookInfo;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;

/**
 * Generic command
 */
class GenericCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'generic';

    /**
     * @var string
     */
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    public function execute()
    {
        if(($msg = $this->getChannelPost()) && $msg->getCommand() === 'puzzle') {
            $chat_id = $msg->getChat()->getId();
            Request::deleteMessage([
                'chat_id' => $msg->getChat()->getId(),
                'message_id' => $msg->getMessageId()
            ]);

            (new PuzzleCommand($this->telegram))->execute($chat_id);
            return Request::emptyResponse();
        }
        else {
            return Request::emptyResponse();
        }
    }
}
