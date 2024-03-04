<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 8/22/2017
 * Time: 12:54 PM
 */

namespace App\Helpers\Repositories;

interface LeadCountsInterface
{
    public function executeReset($campaignCounts, $campaignID, $affiliateID = null, $capType = 'Unlimited', $timezone = null);
}
