<?php

namespace App\Jobs;

use App\CampaignTypeOrder;
use App\MixedCoregCampaignOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Log;

class UpdateCampaignTypeOrder extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $campaign_id;

    protected $old_campaign_type;

    protected $new_campaign_type;

    /**
     * Create a new job instance.
     */
    public function __construct($campaign_id, $old_campaign_type, $new_campaign_type)
    {
        $this->campaign_id = (int) $campaign_id;
        $this->old_campaign_type = $old_campaign_type;
        $this->new_campaign_type = $new_campaign_type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $affected_orders = CampaignTypeOrder::whereIn('campaign_type_id', [$this->old_campaign_type, $this->new_campaign_type])->get();

        foreach ($affected_orders as $order) {

            $type = json_decode($order->campaign_id_order);

            // $log_type = '';
            // $log_campaign_type = $order->campaign_type_id;
            // $log_key = 0;
            // $log_old_type = $type;

            if ($order->campaign_type_id == $this->old_campaign_type) { //Remove from old type
                $key = array_search($this->campaign_id, $type);
                if ($key >= 0) {
                    array_splice($type, $key, 1);
                }

                // $log_type = 'OLD';
                // $log_key = $key;
                // $log_new_type = $type;
            } else { //Add in new type
                $key = floor((count($type)) / 2);
                array_splice($type, $key, 0, $this->campaign_id);

                // $log_type = 'NEW';
                // $log_key = $key;
                // $log_new_type = $type;
            }

            $updated = CampaignTypeOrder::find($order->id);
            $updated->campaign_id_order = json_encode($type);
            $updated->save();

            // Log::info('TYPE: '.$log_type);
            // Log::info('ID: '.$order->id);
            // Log::info('CAMPAIGN TYPE: '.$log_campaign_type);
            // Log::info('KEY: '.$log_key);
            // Log::info('OLD ORDER:');
            // Log::info($log_old_type);
            // Log::info('NEW ORDER:');
            // Log::info($log_new_type);
            // Log::info('--------------');
        }

        //insert campaign in the middle
        $mixeCoregTypes = config('constants.MIXED_COREG_TYPE_FOR_ORDERING');
        //remove the campaign from the order
        $mixedCoregCampaignOrders = MixedCoregCampaignOrder::all();
        $action = 0;

        if (isset($mixeCoregTypes[$this->old_campaign_type]) && ! isset($mixeCoregTypes[$this->new_campaign_type])) {
            //this is removal process
            $action = 1;
        } elseif ((! isset($mixeCoregTypes[$this->old_campaign_type]) && isset($mixeCoregTypes[$this->new_campaign_type])) || $this->old_campaign_type == 0) {
            //this is add process
            $action = 2;
        } else {
            //do nothing
            $action = 0;
        }

        switch ($action) {
            case 1: //remove

                foreach ($mixedCoregCampaignOrders as $mixedCoregCampaignOrder) {
                    $type = json_decode($mixedCoregCampaignOrder->campaign_id_order);
                    $key = array_search($this->campaign_id, $type);

                    if ($key >= 0) {
                        array_splice($type, $key, 1);
                    }

                    $mixedCoregCampaignOrder->campaign_id_order = json_encode($type);
                    $mixedCoregCampaignOrder->save();
                }

                break;

            case 2: //add

                foreach ($mixedCoregCampaignOrders as $mixedCoregCampaignOrder) {
                    $type = json_decode($mixedCoregCampaignOrder->campaign_id_order);
                    $key = floor((count($type)) / 2);
                    array_splice($type, $key, 0, $this->campaign_id);

                    $mixedCoregCampaignOrder->campaign_id_order = json_encode($type);
                    $mixedCoregCampaignOrder->save();
                }

                break;

            default:
                break;
        }
    }
}
