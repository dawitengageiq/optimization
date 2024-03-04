<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebsitesViewTrackerDuplicate extends Model
{
    protected $connection;

    /**
     * Table
     */
    protected $table = 'websites_view_tracker_duplicate';

    /**
     * Editable fields
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'email',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') != 'reports') {
            $this->connection = 'secondary';
        }
    }

    /**
     * Reltionship
     */
    public function website()
    {
        return $this->Belongsto(AffiliateWebsite::class);
    }
}
