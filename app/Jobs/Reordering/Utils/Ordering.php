<?php

namespace App\Jobs\Reordering\Utils;

class Ordering
{
    /**
     * Default variables
     */
    protected $orders = [];

    /**
     * Set campaign order from calculate::class
     */
    public function setOrders(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Get the new campaign ids Order
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Determine the type of reordering then reorder
     */
    public function reorderBy(int $orderBy): void
    {
        if (count($this->orders) <= 0) {
            return;
        }

        switch ($orderBy) {
            //Order campaign ascending
            case 1:
                asort($this->orders);
                break;
                //Order campaign descending
            case 2:
                arsort($this->orders);
                break;
                //Randomize campaign order
            case 3:
                $this->orders = $this->shuffleAssoc($this->orders);
                break;
        }
    }

    /**
     * Random reordering
     */
    protected function shuffleAssoc(array $array): array
    {
        //Initialize
        $new = [];
        //Get array's keys and shuffle them.
        $keys = array_keys($array);
        shuffle($keys);

        //Create same array, but in shuffled order.
        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }

        return $new;
    }
}
