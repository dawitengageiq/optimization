<?php

namespace App\Helpers\Repositories;

use App\LeadDataCsv as Csv;

class LeadCSVDataRepository implements LeadCSVDataInterface
{
    public function saveCSVDATA($leads)
    {
        Csv::create([
            'value' => $leads,
        ]);
    }
}
