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

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use QuizBot\QuizBotCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Generic message command
 *
 * Gets executed when any type of message is sent.
 */
class GenericmessageCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

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
        $message = $this->getMessage()->getText();
        $chat_id = $this->getMessage()->getChat()->getId();

        if( $this->quizBot->isChessMove($message) ) {
            return $this->quizBot->checkAnswer($this->getMessage());
        }
        else {
            $data = [
                'chat_id' => $chat_id,
                'text' => 'I don\'t really understand human language.' . "\n" . 'Wanna do a /quiz or a /random puzzle?',
                'reply_markup' => json_encode(['remove_keyboard' => true]),
            ];
        }
        return Request::sendMessage($data);
    }
}
