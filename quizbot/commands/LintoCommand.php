<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Quizbot\QuizBotCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
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
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        if( $chat_id != getenv('ADMIN') && $chat_id != getenv('SECOND_ADMIN') ) {
            return Request::emptyResponse();
        }
        else {
            $data = [
                'text' => sprintf( "<b>Admin Stats</b>\n--------------\n" .
                                    "DAU <b>%d</b> WAU <b>%d</b> MAU <b>%d</b>\n" .
                                     "Avg Win Percentage: <b>%s</b>\n\n" .
                                    "New Players: <b>%s</b>\n\n" .
                                    "Puzzles Solved Today [<b>%d</b>]\n%s",
                    $this->quizBot->getDAU(),
                    $this->quizBot->getWAU(),
                    $this->quizBot->getMAU(),
                    $this->quizBot->getWinPercentage(),
                    $this->quizBot->getNewUsers(),
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
