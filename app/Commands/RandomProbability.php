<?php

namespace App\Commands;

class RandomProbability extends Command
{
    private $set = [];

    /**
     * Pass the weight and id to randomize probability
     */
    public function __construct($set = [])
    {
        $this->set = $set;
    }

    /**
     * Execute the command.
     */
    public function handle(): int
    {
        $length = 0;
        $multiplier = 1000;

        //Compute the length
        foreach ($this->set as $id => $weight) {
            $length = $length + $weight * $multiplier;
        }

        //distribute the weight into space range. This follows geometric probability.
        $left = 0;
        foreach ($this->set as $id => $weight) {
            $this->set[$id] = $left + $weight * $multiplier;
            $left = $this->set[$id];
        }

        $randomNumber = mt_rand(1, $length);
        $lowerLimit = 0;

        foreach ($this->set as $id => $upperLimit) {
            if ($randomNumber > $lowerLimit && $randomNumber <= $upperLimit) {
                return $id;
            }

            $lowerLimit = $upperLimit;
        }

        //null event
        return null;
    }
}
