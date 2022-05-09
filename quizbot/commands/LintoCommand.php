<?php

namespace QuizBot\Commands;

use Longman\TelegramBot\Request;
use QuizBot\QuizBotCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Admin command
 * Sends out some stats to the admin
 */
class LintoCommand extends QuizBotCommand
{
    /**
     * @var string
     */
    protected $name = 'linto';

    /**
     * @var string
     */
    protected $description = 'Superadmin command';

    /**
     * @var string
     */
    protected $usage = '/linto';

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
        $chat_id = $this->getChatId();

        if( $chat_id != getenv('ADMIN') && $chat_id != getenv('SECOND_ADMIN') ) {
            return Request::emptyResponse();
        }
        else {
            $data = [
                'text' => sprintf( "<b>Admin Stats</b>\n--------------\n" .
                                    "DAU <b>%d</b> WAU <b>%d</b> MAU <b>%d</b>\n" .
                                     "Avg Win Percentage: <b>%s</b>\n\n" .
                                    "New Players: <b>%s</b>\n\n" .
                                    "Polls created/answered today: <b>%d</b> / <b>%d</b>\n\n".
                                    "Puzzles Solved Today [<b>%d</b>]\n%s",
                    $this->quizBot->getDAU(),
                    $this->quizBot->getWAU(),
                    $this->quizBot->getMAU(),
                    $this->quizBot->getWinPercentage(),
                    $this->quizBot->getNewUsers(),
                    $this->quizBot->getTotalPollsToday(),
                    $this->quizBot->getTotalPollAnswersToday(),
                    $this->quizBot->getTotalPuzzlesForToday(),
                    $this->quizBot->getPuzzlesForTodayHTMLList()
                ),
                'chat_id' => $chat_id,
                'parse_mode' => 'html'
            ];
            return Request::sendMessage($data);
        }
    }
}
