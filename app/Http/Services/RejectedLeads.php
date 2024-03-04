<?php

namespace App\Http\Services;

class RejectedLeads
{
    /**
     * Container for leads if exists.
     */
    protected $exists = false;

    /**
     * Filter Collections.
     */
    protected $acceptable = [];

    protected $duplicates = ['duplicate', 'dupe', 'already', 'exist']; // match in array

    protected $prePopIssues = ['invalid', 'does not', 'doesn']; // match in array

    protected $filterIssues = ['does not'];  // !Important Notice: looking for "no" in words but don't match in array

    protected $others = [];  //

    /**
     * Methods to use on each rejection type.
     */
    protected $methods = [
        'acceptable' => 'forDuplicateAndPrePopIssues',
        'duplicates' => 'forDuplicateAndPrePopIssues',
        'pre_pop_issues' => 'forDuplicateAndPrePopIssues',
        'filter_issues' => 'forFilterIssues',
        'others' => 'forOthers',
    ];

    /**
     * An extension to the query made in searchLeads method in AdminController.
     *
     * @return  object eloquent
     */
    public static function searchLeadByRejection(Sql $query, string $rejection = ''): object
    {
        $setting = \App\Setting::where('code', 'high_rejection_keywords')->first();
        $keywords = json_decode($setting->description, true);
        $this->duplicates = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['d']))));
        $this->prePopIssues = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['p']))));
        $this->filterIssues = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['f']))));
        $this->acceptable = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['a']))));

        if ($rejection) {
            $rejectedLeads = new static();

            return $query->with('leadSentResult')->get()->filter(function ($lead) use ($rejectedLeads, $rejection) {
                // Check if value is included in error type
                // If exist, include in the leads in return
                // Else don't include
                $leadInArray = $lead->toArray();
                if ($rejectedLeads->errorTypeExist($leadInArray['lead_sent_result']['value'], $rejection)) {
                    return $lead;
                }
            });
        }

        return $query->get();

    }

    /**
     * Check if exists on rejection type.
     */
    protected function errorTypeExist(string $value, string $rejection = 'duplicates'): Bolean
    {
        $this->others = array_merge($this->duplicates, $this->prePopIssues);  // Don't match in array

        collect($this->{camelCase($rejection)})->each(function ($errorCode) use ($value, $rejection) {
            // check if leads will be included in return as list
            return $this->{$this->methods[$rejection]}($value, $errorCode);
        });

        return $this->exists;
    }

    /**
     * Check if exists on rejection type for duplicate and pre pop iisues.
     *
     * @param  string  $error_code
     */
    protected function forDuplicateAndPrePopIssues(string $value, $errorCode): Bolean
    {
        $this->exists = false;

        // Should match to error code
        // Break the loop if found/ exists,
        if (stripos(strtolower($value), $errorCode) !== false) {
            $this->exists = true;

            return false;
        }
    }

    /**
     * Check if exists on rejection type for filter iisues.
     *
     * @param  string  $error_code
     */
    protected function forFilterIssues(string $value, $errorCode): Bolean
    {
        // Should not match to error code
        if (stripos(strtolower($value), $errorCode) === false) {
            $this->exists = false;

            // Look for no in words, exists if found
            // Dont break the loop, might the other error code will match
            if (stripos(strtolower($value), 'no') !== false) {
                $this->exists = true;
            }

            return true;
        }
        // Break the loop if match, means it belongs to another rejection type(prePopIssues)
        // Doesnt exists on this rejection type
        $this->exists = false;

        return false;
    }

    /**
     * Check if exists on rejection type for others.
     *
     * @param  string  $error_code
     */
    protected function forOthers(string $value, $errorCode): Bolean
    {
        // Should not match to  error code
        // Dont break the loop, might the other error code will match
        if (stripos(strtolower($value), $errorCode) === false) {
            $this->exists = true;

            return true;
        }
        // Break the loop if match, means it belongs to another rejection type
        // Doesnt exists on this rejection type
        $this->exists = false;

        return false;
    }
}
