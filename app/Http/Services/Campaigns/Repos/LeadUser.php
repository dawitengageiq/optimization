<?php

namespace App\Http\Services\Campaigns\Repos;

use Carbon\Carbon;

final class LeadUser
{
    /**
     * Default variables
     */
    protected $model;

    protected $user;

    /**
     * Intantiate, dependency injection of model
     */
    // public function __construct (\Illuminate\Database\Eloquent\Model $model)
    public function __construct(\App\LeadUser $model)
    {
        $this->model = $model;
    }

    /**
     * Save user details
     *
     * @param  array  $user
     * @return void
     */
    public function save($user)
    {
        $this->model->first_name = $user['first_name'];
        $this->model->last_name = $user['last_name'];
        $this->model->email = $user['email'];
        $this->model->birthdate = Carbon::createFromDate($user['dobyear'], $user['dobmonth'], $user['dobday'])->format('Y-m-d');
        $this->model->gender = $user['gender'];
        $this->model->zip = $user['zip'];
        $this->model->city = $user['city'];
        $this->model->state = $user['state'];
        $this->model->ethnicity = 0;
        $this->model->address1 = $user['address'];
        $this->model->address2 = $user['user_agent'];
        $this->model->phone = $user['phone1'].$user['phone2'].$user['phone3'];
        $this->model->source_url = $user['source_url'];
        $this->model->affiliate_id = $user['affiliate_id'];
        $this->model->revenue_tracker_id = $user['revenue_tracker_id'];
        $this->model->ip = $user['ip'];
        $this->model->is_mobile = $user['screen_view'] == 1 ? false : true;
        $this->model->status = ($this->isBot($user['email2'])) ? 3 : 0;
        $this->model->s1 = $user['s1'];
        $this->model->s2 = $user['s2'];
        $this->model->s3 = $user['s3'];
        $this->model->s4 = $user['s4'];
        $this->model->s5 = $user['s5'];

        $this->model->save();
    }

    /**
     * Check if it is a bot
     *
     * @param  string  $email2
     * @return bool
     */
    protected function isBot($email2)
    {
        if ($email2 != '') {
            return true;
        }

        return false;
    }
}
