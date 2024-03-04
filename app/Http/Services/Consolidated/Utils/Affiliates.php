<?php

namespace App\Http\Services\Consolidated\Utils;

use App\Affiliate;

class Affiliates
{
    /**
     * Affilaite container, contains of revenue tracker as index and company name as value.
     *
     * @var array
     */
    protected $affiliates = [];

    /**
     * Has affiliates that were fetch.
     *
     * @var bool
     */
    protected $hasAffiliates = false;

    /**
     * Instantiate.
     * Provide/Inject the needed model.
     */
    public function __construct(Affiliate $model)
    {
        $this->model = $model;
    }

    /**
     * Set the date, use in cron.
     */
    public function setDate(string $date)
    {
        $this->date = $date;
    }

    /**
     * Fetch the affilate records from database.
     * Determine after if the records contain a lists.
     */
    public function pluck()
    {
        $affiliates = $this->model->select('id', 'company')
            ->where('status', 1)
            ->where('type', 1)
            ->orderBy('company')
            ->get();

        if (! $affiliates->isEmpty()) {
            $this->hasAffiliates = true;
            $this->affiliates = $affiliates->toArray();
        }
    }

    /**
     * Check records has affilates/ not empty.
     */
    public function hasRecord()
    {
        return $this->hasAffiliates;
    }

    /**
     * Get the affiliates.
     */
    public function get(): array
    {
        return $this->affiliates;
    }
}
