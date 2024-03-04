<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WebsitesViewTracker extends Model
{
    protected $connection;

    /**
     * Table
     */
    protected $table = 'websites_view_tracker';

    /**
     * Editable fields
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'email',
        'status',
        'payout',
        'affiliate_id',
        'revenue_tracker_id',
        's1',
        's2',
        's3',
        's4',
        's5',
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

    /**
     * Track user views by email within 24 hours.
     */
    public function scopeTrack($query, $email, $websiteID, $payout, $timeInterval): bool
    {
        if ($query->where(['email' => $email, 'status' => 'active'])
            ->where('created_at', '>=', Carbon::now()->subHours($timeInterval))
            ->orderBy('created_at', 'DESC')
            ->exists()
        ) {
            return false;
        }
        // Expires other active records
        $this->where(['email' => $email, 'website_id' => $websiteID, 'status' => 'active'])->update(['status' => 'expired']);

        // insert as new instance
        $this->website_id = $websiteID;
        $this->email = $email;
        $this->payout = $payout;
        $this->save();

        return true;
    }
}
