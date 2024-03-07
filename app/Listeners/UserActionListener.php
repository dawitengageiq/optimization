<?php

namespace App\Listeners;

use App\Events\UserActionEvent;
use App\UserActionLog;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UserActionListener implements ShouldQueue
    // class UserActionListener
{
    protected $dateNow;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->dateNow = Carbon::now();
    }

    /**
     * Handle the event.
     */
    public function handle(UserActionEvent $event)
    {
        $logData = $event->logData;

        if (count($logData) > 0) {
            // Check if the data is batched multiple logs
            if (isset($logData[0]) && is_array($logData[0])) {
                foreach ($logData as $logDatum) {
                    if (is_array($logDatum)) {
                        $this->createUserActionLog($logDatum);
                    }
                }
            } elseif (is_array($logData)) {
                $this->createUserActionLog($logData);
            }
        } else {
            Log::info('UserActionEvent is fired but no data at all.');
        }

        return false;
    }

    private function createUserActionLog($logData)
    {
        return UserActionLog::create([
            'section_id' => isset($logData['section_id']) ? $logData['section_id'] : null, // this is must have
            'sub_section_id' => isset($logData['sub_section_id']) ? $logData['sub_section_id'] : null,
            'reference_id' => isset($logData['reference_id']) ? $logData['reference_id'] : null,
            'user_id' => $logData['user_id'], // this is must have,
            'change_severity' => $logData['change_severity'], // Must have and must be integer.
            'summary' => isset($logData['summary']) ? $logData['summary'] : null,
            'old_value' => isset($logData['old_value']) ? $logData['old_value'] : null,
            'new_value' => isset($logData['new_value']) ? $logData['new_value'] : null,
            'created_at' => isset($logData['created_at']) ? $logData['created_at'] : $this->dateNow->toDateTimeString(),
        ]);
    }
}
