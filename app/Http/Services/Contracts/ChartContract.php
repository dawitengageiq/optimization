<?php

namespace App\Http\Services\Contracts;

interface ChartContract
{
    /**
     * Provide needed data for formating.
     *
     * @var array $data
     */
    public function setData(array $data);

    /**
     * Return the series data in group that will be use in views .
     *
     * @return array
     */
    public function getGroupSeries();

    /**
     * Return the series data in group of categories that will be use in views .
     *
     * @return array
     */
    public function getGroupCategories();

    /**
     * Return the series data in group of categories that will be use in views .
     *
     * @return array
     */
    public function getActualRejection();

    /**
     * Return the data that will be use in views .
     *
     * @return array
     */
    public function getData();

    /**
     * This is for development when cachesd is empty.
     * Debugging purposes
     *
     * @var Bolean $process
     */
    public function dummyData($process = false);

    /**
     * Process on formatting data.
     *
     *
     * @var Bolean $view
     */
    public function formatData($view = true);
}
