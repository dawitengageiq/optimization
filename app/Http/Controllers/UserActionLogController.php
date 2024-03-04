<?php

namespace App\Http\Controllers;

use App\Resources\Resource;
use App\Resources\UserActionLogDatatable as ResourceCollection;
use App\Resources\UserActionLogDetailsDatatable as ResourceDetalsCollection;
use App\UserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserActionLogController extends Controller
{
    protected $additional = [];

    protected $selection = [
        'section_id',
        'sub_section_id',
        'reference_id',
        'user_id',
        'created_at',
    ];

    protected $detailSelection = [
        'section_id',
        'reference_id',
        'summary',
        'old_value',
        'new_value',
        'created_at',
    ];

    public function __construct()
    {
    }

    /**
     * Get all records
     */
    public function all(Request $request, UserActionLog $userLog): Resource
    {
        $this->additional = ['type' => 'UserActionLogAll'];

        $activities = $userLog
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date, SUBSTRING_INDEX(summary, " ", 1) AS action'),
                ...$this->selection
            )
            ->with(['user' => function ($q) {
                return $q->select('id', \DB::raw('CONCAT(first_name, " ", middle_name, " ", last_name) AS full_name'));
            }])
            ->groupBy(['sub_section_id', 'reference_id', 'action', 'date'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Exception ...
        $this->isEmpty($activities->isEmpty(), 'No records\' found. No data was saved in database since the the type is get all user action log(\'UserActionLogAll\').');

        $request->merge(['count' => $activities->count()]);

        return (new ResourceCollection($activities))->additional($this->additional)->toResponse();
    }

    /**
     * [create description]
     *
     * @return [type]
     */
    public function create(Request $request)
    {

    }

    /**
     * Get records by section id
     */
    public function get(Request $request, UserActionLog $userLog, int $sectionID): Resource
    {
        $this->additional = ['type' => 'UserActionLogBySection'];

        // Exception ...
        if ($this->isInvalid(
            [
                'is_numeric' => $sectionID,
            ],
            $request->fullUrl())
        ) {
            return (new ResourceCollection(collect([])))->additional($this->additional)->toResponse(400);
        }

        // Query the selection
        $query = $userLog
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date, SUBSTRING_INDEX(summary, " ", 1) AS action'),
                ...$this->selection
            )
            ->where('section_id', $sectionID)
            ->groupBy(['sub_section_id', 'reference_id', 'action', 'date']);

        //Pass to request the count
        $request->merge(['count' => $query->get()->count()]);

        // Take only the requeste from query
        $activities = $query
            ->with(['user' => function ($q) {
                return $q->select('id', \DB::raw('CONCAT(first_name, " ", middle_name, " ", last_name) AS full_name'));
            }])
            ->skip($request->get('start'))
            ->take($request->get('length'))
            ->orderBy('created_at', 'desc')
            ->get();

        // Exception ...
        $this->isEmpty($activities->isEmpty(), 'No records\' found for section id: '.$sectionID.'.');

        // printR($activities, true);

        return (new ResourceCollection($activities))->additional($this->additional)->toResponse();
    }

    /**
     * Get records by section id and reference id
     */
    public function getByReference(Request $request, UserActionLog $userLog, int $sectionID, int $referenceID): Resource
    {
        $this->additional = ['type' => 'UserActionLogByReference'];

        // Exception ...
        if ($this->isInvalid(
            [
                $sectionID => 'is_numeric',
                $referenceID => 'is_numeric',
            ],
            $request->fullUrl())
        ) {
            return (new ResourceCollection(collect([])))->additional($this->additional)->toResponse(400);
        }

        // Query the selection
        $query = $userLog
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date, SUBSTRING_INDEX(summary, " ", 1) AS action'),
                ...$this->selection
            )
            ->where('section_id', $sectionID)
            ->where('reference_id', $referenceID)
            ->groupBy(['sub_section_id', 'reference_id', 'action', 'date']);

        //Pass to request the count
        $request->merge(['count' => $query->get()->count()]);

        // Take only the requeste from query
        $activities = $query
            ->with(['user' => function ($q) {
                return $q->select('id', \DB::raw('CONCAT(first_name, " ", middle_name, " ", last_name) AS full_name'));
            }])
            ->skip($request->get('start'))
            ->take($request->get('length'))
            ->orderBy('created_at', 'desc')
            ->get();

        // Exception ...
        $this->isEmpty($activities->isEmpty(), 'No records\' found for section id: '.$sectionID.' and reference id: '.$referenceID.'.');

        return (new ResourceCollection($activities))->additional($this->additional)->toResponse();
    }

    /**
     * Get records by section id, reference id and user action
     */
    public function details(Request $request, UserActionLog $userLog, int $sectionID, int $referenceID, string $action): Resource
    {
        $this->additional = ['type' => 'UserActionLogDetails'];

        // Exception ...
        if ($this->isInvalid(
            [
                $sectionID => 'is_numeric',
                $referenceID => 'is_numeric',
            ],
            $request->fullUrl())
        ) {
            return (new ResourceCollection(collect([])))->additional($this->additional)->toResponse(400);
        }

        $query = $userLog->select(
            \DB::raw('SUBSTRING_INDEX(summary, " ", 1) AS action'),
            ...$this->detailSelection
        )
            ->where('section_id', $sectionID)
            ->where('reference_id', $referenceID)
            ->where(\DB::raw('SUBSTRING_INDEX(summary, " ", 1)'), ucwords(strtolower($action)));

        if ($request->has('date')) {
            $query->whereDate('created_at', '=', $request->get('date'));
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        // format records when not empty
        // $activities = tap(
        //     // Exception ...
        //     $this->isEmpty(
        //         $activities->isEmpty(),
        //         'No records\' found for section id: ' . $sectionID . ', reference id: ' . $referenceID . ' and action: ' . $action . '.'
        //     ),
        //     // format
        //     function($isEmpty) use($activities, $sectionID, $action) {
        //             return  $this->formatRecords($isEmpty, $activities, $sectionID, $action);
        //     }
        // );

        // format records when not empty
        $activities = $this->formatRecords(
            // Exception ...
            $this->isEmpty(
                $activities->isEmpty(),
                'No records\' found for section id: '.$sectionID.', reference id: '.$referenceID.' and action: '.$action.'.'
            ),
            $activities, $sectionID, $action
        );

        return (new ResourceDetalsCollection($activities))->additional($this->additional)->toResponse();
    }

    /**
     *  Check if the arguments is valid
     */
    protected function isInvalid(array $args, string $url = null): bool
    {
        foreach ($args as $required => $method) {
            if (method_exists($this, camelCase($method))) {
                if (! $this->{camelCase($method)}($required, $url)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check arguments is numeric.
     */
    protected function isNumeric(int $required, string $url): bool
    {
        if (! is_numeric($required)) {
            $this->additional = array_merge($this->additional, [
                'status' => 400,
                'error' => true,
                'message' => 'Section id should be integer, '.gettype($required).'('.$required.') given in: '.$url.'.',
            ]);

            return false;
        }

        return true;
    }

    /**
     * Check records is empty.
     */
    protected function isEmpty(bool $isEmpty, string $message): bool
    {
        if ($isEmpty) {
            $this->additional = array_merge($this->additional, ['message' => $message]);

            return true;
        }

        return false;
    }

    /**
     * Format...
     *
     * @param  Illuminate\Database\Eloquent\Collection|Illuminate\Support\Collection  $activities
     */
    protected function formatRecords(bool $isEmpty, $activities, int $sectionID, string $action): Collection
    {
        if ($isEmpty || strtolower($action) == 'update') {
            return $activities;
        }

        $format = [];
        foreach ($activities as $activity) {
            $valueField = (strtolower($action) == 'add') ? 'new_value' : 'old_value';

            foreach (array_filter((array) json_decode($activity[$valueField])) as $field => $value) {
                $format[] = [
                    'action' => $activity['action'],
                    'section_id' => $sectionID,
                    'reference_id' => $activity['reference_id'],
                    'summary' => $field,
                    'old_value' => ($valueField == 'old_value') ? $value : '',
                    'new_value' => ($valueField == 'new_value') ? $value : '',
                    'created_at' => $activity['created_at'],
                ];
            }
        }

        return collect($format);
    }
}
