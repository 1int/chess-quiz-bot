<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

use QuizBot\QuizBotRequest;
use Quizbot\QuizBotCommand;

use GuzzleHttp\Client;


class DailyCommand extends QuizBotCommand
{

    /**
     * @var string
     */
    protected $name = 'daily';

    /**
     * @var string
     */
    protected $description = 'Get lichess daily puzzle';

    /**
     * @var string
     */
    protected $usage = '/daily';

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
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $client = new Client();
        $str = $client->get('https://lichess.org')->getBody()->getContents();


        $re = '/\/training\/daily.*data-color=\"([a-z]+)\" data-fen=\"([^\" ]+)/m';


        $counterFile = __DIR__.'/../logs/counter.txt';
        if( !file_exists($counterFile) ) {
            file_put_contents($counterFile, '0');
        }

        $counter = intval(file_get_contents($counterFile));
        file_put_contents($counterFile, $counter+1);

        preg_match($re, $str, $matches);


        if( count($matches) === 3) {

            $fen = $matches[2];
            if( $matches[1] == 'black') {
                $fen .= ' b';
            }
            else {
                $fen .= ' w';
            }
            $fen .=  ' KQkq - 0 1';

            return QuizBotRequest::sendFen($fen, $chat_id,
                sprintf("Puzzle of the day %s\n*%s* to play", date("m/d/Y"), ucfirst($matches[1]))
            );
        }
        else {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => 'Failed to get today\'s lichess puzzle'
            ]);
        }

    }

}