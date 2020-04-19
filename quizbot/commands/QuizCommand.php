<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Entities\ServerResponse;

use QuizBot\QuizBot;
use QuizBot\QuizBotCommand;
use QuizBot\QuizBotRequest;
use RockstarsChess\PuzzleRepository;


/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
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
    public function execute()
    {
        $user_id = $this->getMessage()->getFrom()->getId();
        $quiz = $this->quizBot->getQuizForUser($user_id);

        if( intval($quiz->next) > intval(getenv('QUIZ_PUZZLES')) ) {
            return $this->replyWithText("Ha! You solved all the puzzles! No more left.\nCongratulations!");
        }

        $puzzle = (new PuzzleRepository())->getById($quiz->next);

        $conversation = new Conversation($user_id, $user_id, QuizBot::QUIZ_PUZZLE);
        $conversation->notes = $puzzle->id;
        $conversation->update();

        return QuizBotRequest::sendPuzzle($puzzle, $user_id, !$this->quizBot->recursion);
    }

    /**
     * @return string
     */
    public static function postscriptMessage()
    {
        /*$values = [
            'Hit /next to continue',
            'Click /next to proceed',
            'Wanna go /next quiz puzzle?',
            'Wanna try /next?',
            'Go /next?'
        ];*/

        $values = [
            'Next puzzle. Hit /pause to stop',
            'Here\'s next one. Use /pause to stop.'
        ];

        return $values[array_rand($values)];
    }
}
