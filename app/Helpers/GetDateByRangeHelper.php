<?php

namespace App\Helpers;

use Carbon\Carbon;

class GetDateByRangeHelper
{
    protected $date_from;

    protected $date_to;

    protected $range;

    protected $withTime;

    public function __construct($range, $withTime = false)
    {
        $this->range = $range;
        $this->withTime = $withTime;
        $this->process();
    }

    protected function process()
    {
        switch ($this->range) {
            case 'today':
                $this->date_from = Carbon::now()->startOfDay();
                $this->date_to = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $this->date_from = Carbon::yesterday()->startOfDay();
                $this->date_to = Carbon::yesterday()->endOfDay();
                break;
            case 'week':
                $this->date_from = Carbon::now()->startOfWeek();
                $this->date_to = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $this->date_from = Carbon::now()->startOfMonth();
                $this->date_to = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $this->date_from = Carbon::now()->subMonth()->startOfMonth();
                $this->date_to = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'year':
                $this->date_from = Carbon::now()->startOfYear();
                $this->date_to = Carbon::now()->endOfYear();
                break;
        }

        if (! $this->withTime) {
            $this->date_from = Carbon::parse($this->date_from)->toDateString();
            $this->date_to = Carbon::parse($this->date_to)->toDateString();
        }
    }

    public function date_from()
    {
        return $this->date_from;
    }

    public function date_to()
    {
        return $this->date_to;
    }
}
