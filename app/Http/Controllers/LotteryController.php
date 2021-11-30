<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    const RESULTGENERAL = 6;
    const TYPESCORE = [6, 7, 8, 9, 10];

    private $scores; // Quantidade de dezenas
    private $result; // Resultado
    private $total_games; // Total de Jogos
    private $games; // Jogos

    /**
     * Por limitação do Laravel o construtor não permite a injeção de valores
     * oriundos da rota
     *
     * @param int $scores
     * @param int $total_games
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->setScores($request->route('scores'));
        $this->setTotalGames($request->route('total_games'));
    }

    /** @return mixed  */
    public function getScores(): int
    {
        return $this->scores;
    }

    /**
     * @param mixed $scores
     * @return void
     * @throws Exception
     */
    public function setScores($scores): void
    {
        if (in_array($scores, self::TYPESCORE)) {
            $this->scores = $scores;
        } else {
            throw new Exception("Quantidade de dezena não existe");
        }
    }

    /** @return int  */
    public function getTotalGames(): int
    {
        return $this->total_games;
    }

    /**
     * @param mixed $total_games
     * @return void
     */
    public function setTotalGames($total_games): void
    {
        $this->total_games = $total_games;
    }

    /** @return array  */
    public function getGames(): array
    {
        return $this->games;
    }

    /**
     * @param array $games
     * @return void
     */
    public function setGames(array $games): void
    {
        $this->games = $games;
    }

    /** @return array  */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /** @return void  */
    public function generateBets()
    {
        $games = [];

        for ($i = 0; $i < $this->getTotalGames(); $i++) {
            $games[$i] = $this->bet();
        }

        $this->setGames($games);
    }

    /** @return array  */
    private function bet()
    {
        return $this->generateNumber($this->getScores());
    }

    /** @return void  */
    public function result()
    {
        $this->setResult($this->generateNumber(self::RESULTGENERAL));
    }

    /**
     * @param mixed $quantity
     * @return array
     */
    private function generateNumber($quantity)
    {
        $array = [];

        for ($i = 0; $i < $quantity; $i++) {
            $number = mt_rand(1, 60);
            in_array($number, $array) ? $i-- : $array[$i] = $number;
        }

        sort($array);
        return $array;
    }

    /** @return mixed  */
    public function index()
    {
        $this->generateBets();
        $this->result();

        $array = [];
        $array['result'] = implode(" - ", $this->getResult());

        foreach($this->getGames() as $key => $game) {
            $array['bets'][$key]['bet'] = implode(" - ", $game);

            $numbers = $this->getBetResult($game);

            $array['bets'][$key]['hit'] = count($numbers) != 0 ?? count($numbers);
            $array['bets'][$key]['numbers'] = implode(" - ", $numbers) ?? implode(" - ", $numbers);
        }

        return view('welcome', [ 'results' => $array ]);
    }

    /**
     * @param mixed $game
     * @return array
     */
    private function getBetResult($game)
    {
        $numbers = array_intersect($game, $this->getResult());
        sort($numbers);
        return $numbers;
    }
}
