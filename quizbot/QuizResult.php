<?php


namespace QuizBot;


class QuizResult
{
    public $user_id;
    public $started_at;
    public $correct;
    public $incorrect;
    public $streak;
    public $next;
    public $elo;

    /**
     * Get initial result (i.e. somebody started the quiz)
     * @return QuizResult
     */
    public static function InitialResult()
    {
        $ret = new QuizResult();
        $ret->correct = 0;
        $ret->incorrect = 0;
        $ret->streak = 0;
        $ret->next = 1;
        $ret->elo = 1500;
        return $ret;
    }
}