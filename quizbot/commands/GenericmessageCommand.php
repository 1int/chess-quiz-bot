<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace QuizBot\Commands;

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
    public function execute(): ServerResponse
    {
        $message = $this->getMessage()->getText();
        $chat_id = $this->getChatId();

        if( $this->quizBot->isChessMove($message) ) {
            return $this->quizBot->checkAnswer($this->getMessage());
        }
        elseif(($chat = $this->getMessage()->getChat()) && $chat->isGroupChat()){
            $users = $this->getMessage()->getNewChatMembers();
            foreach($users as $user) {
               if($user->getUsername() === getenv('BOTNAME')) {
                   return Request::sendMessage([
                       'chat_id' => $chat_id,
                       'text' => "Hey! I am Chess Quiz Bot. Happy to be in your group!\nUse /puzzle to get a chess puzzle from me."
                   ]);
               }
            }
            return Request::emptyResponse();
        }
        else {
            //Bot no reply to all messages
            /*return  Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'I don\'t really understand human language.' . "\n" . 'Wanna do a /quiz or a /random puzzle?',
                'reply_markup' => json_encode(['remove_keyboard' => true]),
            ]);*/
        }
    }
}
