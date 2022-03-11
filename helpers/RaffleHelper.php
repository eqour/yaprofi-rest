<?php

namespace app\helpers;

class RaffleHelper
{
    public function process(array $participants, array $prizes): array
    {
        $winners = [];

        while (!empty($participants) || !empty($prizes)) {
            $winner = $this->getRandomItem($participants);
            $prize = $this->getRandomItem($prizes);

            $winners[] = [
                'winner' => $winner,
                'prize' => $prize
            ];
        }

        return $winners;
    }

    private function getRandomItem(array &$arr): array
    {
        $guess = random_int(0, count($arr) - 1);
        $item = $arr[array_keys($arr)[$guess]];
        unset($arr[array_keys($arr)[$guess]]);
        return $item;
    }
}
