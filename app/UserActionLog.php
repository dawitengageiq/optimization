<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    protected $connection;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_action_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'section_id',
        'sub_section_id',
        'reference_id',
        'user_id',
        'change_severity',
        'summary',
        'old_value',
        'new_value',
        'created_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearchHistoryLogsWithUser($query, $params)
    {
        $query->select('user_action_logs.*', DB::raw('CONCAT(users.first_name, " ", users.middle_name, " ", users.last_name, " (",users.id,") ") AS full_name'));
        $query->join('users', 'user_action_logs.user_id', '=', 'users.id');

        if (array_key_exists('section_id', $params)) {
            if (is_null($params['section_id'])) {
                $query->whereNull('section_id');
            } elseif (! empty($params['section_id'])) {
                $query->where('section_id', '=', $params['section_id']);
            }
        }

        if (array_key_exists('sub_section_id', $params)) {
            if (is_null($params['sub_section_id'])) {
                $query->whereNull('sub_section_id');
            } elseif (! empty($params['sub_section_id'])) {
                $query->where('sub_section_id', '=', $params['sub_section_id']);
            }
        }

        if (array_key_exists('reference_id', $params)) {
            if (is_null($params['reference_id'])) {
                $query->whereNull('reference_id');
            } elseif (! empty($params['reference_id'])) {
                $query->where('reference_id', '=', $params['reference_id']);
            }
        }

        if (isset($params['user_id']) && ! empty($params['user_id'])) {
            $query->where('user_id', '=', $params['user_id']);
        }

        if (isset($params['change_severity']) && ! empty($params['change_severity'])) {
            $query->where('change_severity', '=', $params['change_severity']);
        }

        if (array_key_exists('summary', $params)) {
            if (is_null($params['summary'])) {
                $query->whereNull('summary');
            } elseif (! empty($params['summary'])) {
                $query->where('summary', 'like', '%'.$params['summary'].'%');
            }
        }

        if (array_key_exists('old_value', $params)) {
            if (is_null($params['old_value'])) {
                $query->whereNull('old_value');
            } elseif (! empty($params['old_value'])) {
                $query->where('old_value', 'like', '%'.$params['old_value'].'%');
            }
        }

        if (array_key_exists('new_value', $params)) {
            if (is_null($params['new_value'])) {
                $query->whereNull('new_value');
            } elseif (! empty($params['new_value'])) {
                $query->where('new_value', 'like', '%'.$params['new_value'].'%');
            }
        }

        if ((isset($params['date_from']) && ! empty($params['date_from'])) && (isset($params['date_to']) && ! empty($params['date_to']))) {
            // $query->where('user_action_logs.created_at','>=',$params['date_from']);
            // $query->where('user_action_logs.created_at','<=',$params['date_to']);
            $query->whereRaw('DATE(user_action_logs.created_at) >= DATE(?) AND DATE(user_action_logs.created_at) <= DATE(?)', [$params['date_from'], $params['date_to']]);
        }

        // if there is a presence of searchbox
        if (isset($params['search']['value']) && ! empty($params['search']['value'])) {
            $searchTerm = $params['search']['value'];

            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('summary', 'like', '%'.$searchTerm.'%');
                $subQuery->orWhere('users.first_name', 'like', '%'.$searchTerm.'%');
                $subQuery->orWhere('users.middle_name', 'like', '%'.$searchTerm.'%');
                $subQuery->orWhere('users.last_name', 'like', '%'.$searchTerm.'%');

                if (is_numeric($searchTerm)) {
                    $subQuery->orWhere('user_action_logs.id', '=', $searchTerm);
                    $subQuery->orWhere('user_action_logs.reference_id', '=', $searchTerm);
                }
            });
        }

        // For order by
        if (isset($params['order_column']) && ! empty($params['order_column'])) {
            $orderDirection = 'asc';

            if (isset($params['order_direction']) && ! empty($params['order_direction'])) {
                $orderDirection = $params['order_direction'];
            }

            $query->orderBy($params['order_column'], $orderDirection);
        }

        // always order the records by date descending
        $query->orderBy('user_action_logs.created_at', 'desc');

        return $query;
    }

    public function scopeCampaignPayoutHistory($query, $campaign, $start, $length)
    {
        $query->select(DB::RAW("user_action_logs.created_at, sub_section_id, user_id, CONCAT(users.first_name,' ',users.last_name) as user_name, GROUP_CONCAT(summary SEPARATOR '[{S}]') as summary, GROUP_CONCAT(new_value SEPARATOR '[{S}]') as new_value, (CASE WHEN summary LIKE 'Add%' THEN 'Add' WHEN summary LIKE 'Update%' THEN 'Update' ELSE 'Delete' END) as type"));

        $query->leftJoin('users', 'user_action_logs.user_id', '=', 'users.id');

        $query->where('reference_id', $campaign)
            ->where('section_id', 3)
            ->where(function ($query) {
                $query->where('sub_section_id', null)
                    // ->orWhere('sub_section_id', 34)
                    ->orWhere('sub_section_id', 31);
            })
            ->where(function ($query) {
                $query->where('summary', 'like', '%rate%')
                    ->orWhere('summary', 'like', '%received%')
                    ->orWhere('summary', 'like', '%payout%');
            });
        if ($start != null) {
            $query->skip($start);
        }

        if ($length != null) {
            $query->take($length);
        }

        $query->groupBy(['user_action_logs.created_at', 'reference_id', 'section_id', 'sub_section_id', 'user_id', 'type']);

        $query->orderBy('user_action_logs.created_at', 'DESC');

        return $query;
    }
}
