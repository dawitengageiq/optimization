<?php

namespace App\Console\Commands;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Lead;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Console\Command;
use Sabre\Xml\Reader;

class GenerateInternalAffiliateReportsOld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:internal-affiliate-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command will generate affiliate reports for the current date';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info(Carbon::now()->toDateTimeString());
        $this->info('Initiating generating of internal affiliate reports...');

        $dateNow = Carbon::now()->subDay();
        $dateTomorrow = Carbon::now();

        $dateNowStr = $dateNow->toDateTimeString();
        $dateTomorrowStr = $dateTomorrow->toDateTimeString();

        //1 = internal type
        $params['type'] = 1;

        $revenueTrackers = AffiliateRevenueTracker::withAffiliates($params)->get();

        foreach ($revenueTrackers as $tracker) {
            $this->info('Processing for '.$tracker->affiliate_name.' with revenue_tracker_id: '.$tracker->revenue_tracker_id);

            //$report = new AffiliateReport;
            $report = AffiliateReport::firstOrNew([
                'affiliate_id' => $tracker->affiliate_id,
                'affiliate_revenue_tracker_id' => $tracker->revenue_tracker_id,
                'created_at' => $dateNow->toDateString(),
            ]);

            $report->affiliate_id = $tracker->affiliate_id;
            $report->affiliate_revenue_tracker_id = $tracker->revenue_tracker_id;
            $report->affiliate_name = $tracker->affiliate_name;
            $report->revenue_tracker_name = $tracker->website;
            $report->type = 1;
            $report->clicks = 0;
            $report->payout = 0.0;
            $report->leads = 0;
            $report->revenue = 0.0;
            $report->we_get = 0.0;
            $report->margin = 0.0;
            $report->created_at = $dateNowStr;

            $clicks = 0;
            $payout = 0.0;
            $leads = 0;
            $revenue = 0.0;
            $we_get = 0.0;
            $margin = 0.0;

            //get the lead statistics
            $leadStats = Lead::affiliateRevenueStats([
                'affiliate_id' => $tracker->revenue_tracker_id,
                'date' => $dateNowStr])->first();

            if (isset($leadStats->leads)) {
                $leads = $leads + $leadStats->leads;
            }

            if (isset($leadStats->received)) {
                $revenue = $revenue + floatval($leadStats->received);
            }

            /*
            if(isset($leadStats->payout))
            {
                $payout = $payout + $leadStats->payout;
            }
            */

            //get cake data
            $prefix = config('constants.CAKE_SABRE_PREFIX');
            $url = config('constants.AFFILIATES_CAKE_API_BASE_URL_WITH_API_KEY').'&start_date='.urlencode($dateNowStr).'&end_date='.urlencode($dateTomorrowStr).'&affiliate_id='.$tracker->revenue_tracker_id;

            $reader = new Reader();
            $reader->elementMap = [
                $prefix.'affiliate_summary_response' => 'Sabre\Xml\Element\KeyValue',
                $prefix.'affiliate_summary' => 'Sabre\Xml\Element\KeyValue',
            ];

            $curl = new Curl();
            $curl->get($url);

            $reader->xml($curl->response);
            $response = $reader->parse();

            $affiliates = $response['value'][$prefix.'affiliates'];

            $cakeRevenue = 0.0;

            //get the revenue of the affiliates
            if ($affiliates && count($affiliates) > 0) {
                //this means the reponse has affiliate data
                foreach ($affiliates as $value) {
                    //var_dump($value['value'][$prefix.'clicks']);
                    //var_dump($value['value'][$prefix.'revenue']);
                    //var_dump($value['value'][$prefix.'cost']);

                    $clicks = $clicks + intval($value['value'][$prefix.'clicks']);
                    $payout = $payout + floatval($value['value'][$prefix.'cost']);
                    $cakeRevenue = $cakeRevenue + floatval($value['value'][$prefix.'revenue']);
                }
            }

            $totalRevenue = ($revenue + $cakeRevenue);
            $this->info('TOTAL REVENUE: '.$totalRevenue);
            $this->info('PAYOUT: '.$payout);
            $we_get = $totalRevenue - $payout;
            $this->info('WE_GET: '.$we_get);

            if ($we_get > 0.0) {
                $margin = ($we_get / $totalRevenue) * 100.0;
                $margin = round($margin, 2);
            }

            $this->info('MARGIN: '.$margin);

            $report->clicks = $clicks;
            $report->payout = $payout;
            $report->leads = $leads;
            $report->revenue = $totalRevenue;
            $report->we_get = $we_get;
            $report->margin = $margin;

            $report->save();

            /*
            $this->info('CLICKS: '.$report->clicks);
            $this->info('PAYOUT: '.$report->payout);
            $this->info('LEADS: '.$report->leads);
            $this->info('REVENUE: '.$report->revenue);
            $this->info('WE_GET: '.$report->payout);
            $this->info('MARGIN: '.$report->margin);
            */
            //var_dump($report);
        }

        $this->info('Generate Internal Affiliate Report is finished!');
    }
}
