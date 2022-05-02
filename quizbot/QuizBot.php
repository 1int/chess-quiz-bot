<?php

namespace QuizBot;

use Longman\TelegramBot\Commands\SystemCommands\RandomCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram as TelegramBot;
use Longman\TelegramBot\Exception\TelegramException;
use RockstarsChess\PuzzleRepository;

/**
 * Class QuizBot
 * Main bot class.
 * Contains all bot-specific code and also all default telegram bot methods.
 *
 * @package QuizBot
 */
class QuizBot extends TelegramBot
{
    const RANDOM_PUZZLE = 'random';
    const QUIZ_PUZZLE = 'quiz';

    const EMOJI_MAP = [ 'N'=>'♞', 'Q' => '♛', 'R' => '♜', 'B' => '♝', 'P' => '♟', 'K'=>'♚'];

    /**
     * @var bool
     * Indicates if one command triggered another
     */
    public $recursion = false;

    /**
     * @param string $message
     * @return bool
     */
    public function isChessMove($message)
    {
        if( $message === '0-0' || $message === '0-0-0' ) {
            return true;
        }

        // pawn move (could be a promotion)
        if(preg_match('/^[a-h][1-8a-h](=[NQRB])?[+]?$/', $message)) {
            return true;
        }


        $char = mb_substr($message, 0, 1);
        return array_search($char, self::EMOJI_MAP) !== false;
    }

    /**
     * @param string $message
     * @return string
     */
    public function replaceEmojis($message)
    {
        foreach(self::EMOJI_MAP as $key => $value) {
            $message = mb_ereg_replace($value, $key, $message);
        }
        return $message;
    }

    /**
     * @param $message
     * @return false|string
     */
    public function setEmojis($message)
    {
        foreach(self::EMOJI_MAP as $key => $value) {
            $message = mb_ereg_replace($key, $value, $message);
        }
        return $message;
    }

    /**
     * @return string
     */
    public static function getFenCacheDirectory()
    {
        return __DIR__ . '/../runtime/fen';
    }

    /**
     * @param $a string the source answer (i.e. 1. ...Nxd7)
     * @return string the pretty answer with emojis
     */
    public static function toHumanReadableAnswer($a)
    {
        if( $a == '0-0' || $a == '0-0-0' ) {
            return $a;
        }

        if( array_key_exists($a[0], self::EMOJI_MAP) ) {
            return self::EMOJI_MAP[$a[0]] . mb_substr($a, 1);
        }
        else { // pawn move
            return /*'♟' . */ $a;
        }
    }

    public function getCorrectText()
    {
        $values = [
            'Correct!', 'You\'re right!',  'Perfect!',  'Well done!', 'Good job!', 'Perfecto!',
            'Nicely done.', 'Yes.', '+1'
        ] ;
        return '✅' . ' ' . $values[array_rand($values)];
    }

    public function getIncorrectText()
    {
        $values = [
            'Wrong.', 'Not quite.',  'A miss.',  'Unfortunately not.', 'Ooops', 'Ah, sorry.',
            'Nope.'
        ];
        return '❌' . ' ' . $values[array_rand($values)];
    }

    /**
     * @param Message $message
     * @return ServerResponse
     * @throws TelegramException
     */
    public function checkAnswer(Message $message)
    {
        // ✅ ❌ 🔥
        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();
        $conversation = new Conversation(
            $user_id,
            $message->getChat()->getId()
        );
        $cmd = $conversation->getCommand();

        if( $conversation->exists()  && ($cmd == self::QUIZ_PUZZLE || $cmd == self::RANDOM_PUZZLE )) {
            $answer = $this->replaceEmojis($message->getText());
            $id = $conversation->notes;

            $puzzles = new PuzzleRepository();
            $puzzle = $puzzles->getById($id);
            $isCorrect = $puzzle->isCorrectAnswer($answer);

            if( $isCorrect ) {
                $ret = $this->getCorrectText();
                $this->incCorrectCount($message);
            }
            else {
                $ret = $this->getIncorrectText();
                $this->incIncorrectCount($message);
            }

            $conversation->stop();
            if($cmd == self::RANDOM_PUZZLE ) {
                $ret .= "\n" . RandomCommand::postscriptMessage();
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $ret,
                    'parse_mode' => 'markdown'
                ]);
            }
            elseif($cmd == self::QUIZ_PUZZLE) {
                $quiz = $this->getQuizForUser($user_id);

                $change = $this->calculateRatingChange($quiz->elo, $puzzle->elo, $isCorrect);
                $this->updateQuizResultsForUser($user_id, $isCorrect, $change);

                $ret .= "\nCorrect answer: *". $this->setEmojis($puzzle->getAnswer()) . '*';

                $newElo = $quiz->elo + $change;

                $ret .= "\n\n📈 *{$newElo}* (" . ($change > 0?'+':'') . "{$change})";
                $ret .= "  🏆 " . $this->getUserPosition($newElo) . " of " .  $this->getTotalUsers();

                $puzzle->wrong_answers += $isCorrect ? 0 : 1;
                $puzzle->correct_answers += $isCorrect ? 1 : 0;
                $puzzle->elo += (-1*$change);
                $puzzles->updatePuzzleStats($puzzle);
                $this->recursion = true;

                $data = [
                    'text' => $ret,
                    'chat_id' => $chat_id,
                    'parse_mode' => 'markdown'
                ];

                Request::sendMessage($data);
                return $this->executeCommand(self::QUIZ_PUZZLE);
            }
        }
        else {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' =>  'I think I lost track of what we\'re doing. Wanna continue the /quiz or solve /random puzzle?',
            ]);
        }
    }

    /**
     * @param Message $message
     * @return void
     */
    public function incCorrectCount(Message $message)
    {
        if( $message->getChat()->getId() != getenv('ADMIN') ) {
            $this->pdo->query('UPDATE `stats` SET value = value + 1 where metric=\'total_correct\'');
        }
    }

    /**
     * @param Message $message
     * @return void
     */
    public function incIncorrectCount(Message $message)
    {
        if( $message->getChat()->getId() != getenv('ADMIN') ) {
            $this->pdo->query('UPDATE `stats` SET value = value + 1 where metric=\'total_incorrect\'');
        }
    }

    /**
     * @return string
     */
    public function getWinPercentage()
    {
        $correct = intval($this->pdo->query('SELECT `value` from  `stats` WHERE metric=\'total_correct\'')->fetchAll()[0]['value']);
        $incorrect = intval($this->pdo->query('SELECT `value` from  `stats` WHERE metric=\'total_incorrect\'')->fetchAll()[0]['value']);

        return sprintf('%.2f%%', 100*$correct/($correct+$incorrect));
    }

    /**
     * @return string
     */
    public function getNewUsers()
    {
        $users = $this->pdo->query("select first_name, username from user where DATE(created_at)=DATE(NOW())")->fetchAll();
        $ret = [];
        foreach($users as $user) {
            if ($user['username'] ) {
                $ret[] = $user['first_name'] . ' @' . $user['username'];
            }
            else {
                $ret[] = $user['first_name'];
            }
        }
        return implode(', ', $ret);
    }

    private function getTotalActiveUsers($days): int
    {
        $sql = "select COUNT(DISTINCT user_id) as cnt from conversation WHERE
               DATEDIFF(NOW(), created_at) < $days AND user_id <> '" .  getenv('ADMIN') . "'";
        return $this->pdo->query($sql)->fetchAll()[0]['cnt'] ?? 0;
    }
    
    public function getDAU()
    {
        return $this->getTotalActiveUsers(1);
    }

    public function getWAU()
    {
        return $this->getTotalActiveUsers(7);
    }

    public function getMAU()
    {
        return $this->getTotalActiveUsers(30);
    }

    public function getTotalPuzzlesForToday(): int
    {
        $sql = "select count(*) as cnt from conversation WHERE user_id <> '" . getenv('ADMIN') . "' AND DATE(`created_at`) = DATE(NOW()) " .
            " AND (`command` = 'random' OR `command`='quiz')";
        return $this->pdo->query($sql)->fetchAll()[0]['cnt'];
    }

    /**
     * @return string
     */
    public function getPuzzlesForTodayHTMLList()
    {
        $sql = "select 
                    CONCAT(COALESCE(CONCAT(user.first_name, ' '), ''), COALESCE(user.last_name, ''), COALESCE(CONCAT(' (@', user.username, ')'), '')) as uname, 
                    count(*) as cnt 
                    FROM conversation LEFT join user on conversation.user_id = user.id 
                    WHERE user_id <> " . getenv('ADMIN') .  " AND DATE(conversation.`created_at`) = DATE(NOW())
                    AND (`command` = 'random' OR `command`='quiz') GROUP BY user_id
                    ORDER BY cnt DESC";

        $rows = $this->pdo->query($sql)->fetchAll();
        if($rows) {
            $ret = '';
            foreach($rows as $row) {
                $ret .= '<b>' . $row['cnt'] .  '</b> ' . $row['uname'] . "\n";
            }
            return $ret;
        }
        return '';
    }

    public function getQuizForUser($user_id)
    {
        $sql = "SELECT * FROM quiz_score WHERE user_id=:user_id";
        $sql = $this->pdo->prepare($sql);
        $sql->bindValue(':user_id', $user_id);
        $sql->execute();
        $ret = $sql->fetchObject(QuizResult::class);
        if( !$ret ) {
            $sql = "INSERT INTO quiz_score (user_id) VALUES (:user_id)";
            $sql = $this->pdo->prepare($sql);
            $sql->bindValue(':user_id', $user_id);
            $sql->execute();

            $ret = QuizResult::InitialResult();
            $ret->user_id = $user_id;
        }
        return $ret;
    }

    /**
     * Record the quiz result and move to next quiz puzzle
     *
     * @param string $user_id
     * @param bool $isCorrect
     * @param int $ratingChange
     */
    public function updateQuizResultsForUser($user_id, $isCorrect, $ratingChange = 0)
    {
        if( $isCorrect ) {
            $sql = "UPDATE quiz_score set `next`=`next` +1, correct=correct+1, streak=streak+1, elo=elo+$ratingChange WHERE user_id=:uid";
        }
        else {
            $sql = "UPDATE quiz_score set `next`=`next` +1, incorrect=incorrect+1, streak=0, elo=elo+$ratingChange WHERE user_id=:uid";
        }
        $sql = $this->pdo->prepare($sql);
        $sql->bindValue(':uid', $user_id);
        $sql->execute();
    }

    /**
     * @param int $player_rating
     * @param int $puzzle_rating
     * @param bool $won
     * @return float|int
     */
    public function calculateRatingChange($player_rating, $puzzle_rating, $won)
    {
        $ea = 1 / (1 + pow(10, ($puzzle_rating-$player_rating)/400));
        $k = 100;
        return intval($k *(($won? 1:0) - $ea));
    }

    public function getTotalUsers()
    {
        return DB::getPdo()->query("select count(*) from quiz_score where 1")->fetchAll()[0][0];
    }

    public function getUserPosition($elo)
    {
        return intval(DB::getPdo()->query("select count(*) from quiz_score where elo > $elo")->fetchAll()[0][0]) + 1;
    }

}