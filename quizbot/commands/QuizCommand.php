<?php

namespace QuizBot\Commands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Entities\ServerResponse;

use QuizBot\QuizBot;
use QuizBot\QuizBotCommand;
use QuizBot\QuizBotRequest;
use RockstarsChess\PuzzleRepository;


/**
 * Quiz Command
 *
 * Starts or continues the quiz.
 * Quiz is a series of chess puzzles that affects rating.
 * Once you solve one puzzle the bot immediately sends the next one
 * without waiting for the user to issue the command again
 */
class QuizCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = 'quiz';

    /**
     * @var string
     */
    protected $description = 'Start or continue the quiz';

    /**
     * @var string
     */
    protected $usage = '/quiz';

    /**
     * @var string
     */
    protected $version = '1.1.0';

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
        $user_id = $this->getMessage()->getFrom()->getId();
        $chat_id = $this->getMessage()->getChat()->getId();
        $quiz = $this->quizBot->getQuizForUser($user_id);

        if( intval($quiz->next) > intval(getenv('QUIZ_PUZZLES')) ) {
            return $this->replyWithText("Ha! You solved all the puzzles! No more left.\nCongratulations!");
        }

        $puzzle = (new PuzzleRepository())->getById($quiz->next);

        $conversation = new Conversation($user_id, $chat_id, QuizBot::QUIZ_PUZZLE);
        $conversation->notes = $puzzle->id;
        $conversation->update();

        // 15% chance to trigger /top command when playing on rating
        if($this->quizBot->recursion) {
            if(rand(1, 100) >= 85) {
                $topCommand = new TopCommand($this->telegram);
                $topCommand->setChatId($chat_id)->execute();
            }
        }

        return QuizBotRequest::sendPuzzle($puzzle, $chat_id, $this->quizBot->recursion);
    }

    /**
     * @return string
     */
    public static function postscriptMessage()
    {
        $values = [
            'Next puzzle. Hit /pause to stop',
            'Here\'s next one. Use /pause to stop.'
        ];

        return $values[array_rand($values)];
    }
}
