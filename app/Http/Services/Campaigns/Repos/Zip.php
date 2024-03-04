<?php

namespace App\Http\Services\Campaigns\Repos;

final class Zip
{
    /**
     * Default variables
     */
    protected $zip;

    protected $state = '';

    protected $city = '';

    protected $model;

    /**
     * Intantiate, dependency injection of model
     */
    // public function __construct (\Illuminate\Database\Eloquent\Model $model)
    public function __construct(\App\ZipCode $model)
    {
        $this->model = $model;
    }

    /**
     * Set data base on zip code
     *
     * @param  int|string|mixed  $zip
     */
    public function set($zip)
    {
        if ($zip = $this->model->select('city', 'state')->where('zip', $zip)->first()) {
            $this->zip = $zip;
            $this->city = $zip->city;
            $this->state = $zip->state;
        }
    }

    /**
     * Get detals
     */
    public function details()
    {
        return $this->zip;
    }

    /**
     * Get city
     */
    public function city()
    {
        return $this->city;
    }

    /**
     * Get state
     */
    public function state()
    {
        return $this->state;
    }
}
