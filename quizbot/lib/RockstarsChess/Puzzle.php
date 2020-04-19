<?php


namespace RockstarsChess;

class Puzzle
{
    public $id;

    protected $options;
    protected $fen;
    protected $answer;
    protected $dotdotdot;

    public $elo;
    public $wrong_answers;
    public $correct_answers;

    /**
     * @return string
     */
    public function getFen()
    {
        $fen = $this->fen;
        $fen .= $this->dotdotdot ? ' b' : ' w';
        $fen .= ' KQkq - 0 1';
        return $fen;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return explode(' ', $this->options);
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return bool
     */
    public function isWhiteToMove()
    {
        return !$this->dotdotdot;
    }

    /**
     * @param string $a
     * @return bool
     */
    public function isCorrectAnswer($a)
    {
        return $a == $this->answer;
    }

}