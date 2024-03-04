<?php

namespace App\Commands;

use App\User;
use DB;

class GetAvailableUsers extends Command
{
    protected $db;

    protected $id;

    /**
     * Create a new command instance.
     */
    public function __construct($db, $id)
    {
        $this->db = $db; //the table name to check if user exists there
        $this->id = $id; //includes this id to list even it already exists in table
    }

    /**
     * Get users not existing to the table $db.
     */
    public function handle(): void
    {
        return User::leftJoin($this->db, 'users.id', '=', $this->db.'.id')
            ->where('account_type', 1)
            ->whereNull($this->db.'.id')
            ->orWhere($this->db.'.id', $this->id)
            ->select('users.id as uid', DB::raw('CONCAT(CONCAT(users.first_name," ", users.last_name)," - ", users.email) AS full_name'))
            ->pluck('full_name', 'uid')
            ->toArray();
    }
}
