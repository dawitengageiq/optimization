<?php

namespace App\Http\Services\Charts\Utils;

trait DummyData
{
    /**
     * This is for development when cachesd is empty.
     * Debugging purposes
     *
     * @var Bolean
     */
    public function dummyData($process = false)
    {
        if ($process) {
            $this->data = $this->config['dummy_serialized_data'];
            $this->offsetData(28);
            $this->formatData();
        }
    }
}
