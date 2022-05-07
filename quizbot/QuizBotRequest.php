<?php


namespace QuizBot;

use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;
use RockstarsChess\Puzzle;

class QuizBotRequest
{
    /**
     * @param Puzzle $puzzle
     * @param string $chat_id
     * @param bool $recursion
     * @return ServerResponse
     * @throws TelegramException
     */
    public static function sendPuzzle(Puzzle $puzzle, $chat_id, $recursion = true)
    {
        list($options, $_) = QuizBot::generateOptions($puzzle);

        // 2x2 keyboard
        $keyboard = [
            [new KeyboardButton($options[0]), new KeyboardButton($options[1])],
            [new KeyboardButton($options[2]), new KeyboardButton($options[3])]
        ];

        $keyboard_config = [
            'keyboard' => $keyboard,
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        ];

        if($recursion) {
            $caption = sprintf("Puzzle %s. *%s* to move.", $puzzle->id, $puzzle->isWhiteToMove() ? 'White':'Black');
            $caption .= "\n_Hit /pause to stop_";
        }
        else {
            $caption = sprintf("Puzzle %s\n*%s* to move", $puzzle->id, $puzzle->isWhiteToMove() ? 'White':'Black');
        }

        $fen = $puzzle->getFen();

        return self::sendFen($fen, $chat_id, $caption, $keyboard_config);
    }

    /**
     * @param string $fen
     * @param string $chat_id
     * @param string $caption
     * @param array|null $reply_markup
     * @return ServerResponse
     * @throws TelegramException
     */
    public static function sendFen($fen, $chat_id, $caption = '', $reply_markup = null)
    {
        $imgFen = str_replace(['/', ' '], '_', $fen);
        $fullPath = QuizBot::getFenCacheDirectory() . '/' . $imgFen . '.png';

        if( !file_exists($fullPath) ) {
            shell_exec("cd " . __DIR__ . "/../fen2img && python main.py -o $imgFen $fen");
        }

        $data = [
            'chat_id' => $chat_id,
            'photo'   => Request::encodeFile($fullPath),
            'caption' => $caption,
            'parse_mode' => 'markdown'
        ];

        if( $reply_markup ) {
            $data['reply_markup'] = json_encode($reply_markup);
        }

        return Request::sendPhoto($data);
    }

    public static function sendPoll(Puzzle $puzzle, $chat_id, $isAnonymous)
    {
        self::sendFen($puzzle->getFen(), $chat_id);

        list($options, $correct_option) = QuizBot::generateOptions($puzzle);

        return Request::sendPoll([
            'chat_id' => $chat_id,
            'question' => sprintf("Puzzle %s. %s to move.", $puzzle->id, $puzzle->isWhiteToMove() ? 'White':'Black'),
            'type' => 'quiz',
            'options' => json_encode($options),
            'correct_option_id' => $correct_option,
            'is_anonymous' => $isAnonymous
        ]);
    }
}