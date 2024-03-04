<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\AffiliateCampaign;
use App\AffiliateCampaignRequest;
use App\Campaign;
use App\CampaignPostingInstruction;
use App\Http\Requests;
use App\Jobs\Email\AdminApplyToRunRequest;
use App\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Mail;

class AffiliateRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $totalFiltered = AffiliateCampaignRequest::count();

        $requestsData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $request_status = $inputs['status'];

        $columns = [
            // datatable column index  => database column name
            0 => 'affiliate_campaign_requests.updated_at',
            1 => 'affiliate_campaign_requests.created_at',
            2 => 'affiliates.company',
            3 => 'campaigns.name',
        ];

        $selectSQL = 'SELECT affiliates.id,affiliates.company,affiliates.website_url,affiliates.address,affiliates.city,affiliates.state,affiliates.zip,affiliates.phone,affiliates.description,affiliates.status,users.email AS user_email FROM affiliates LEFT JOIN users ON users.affiliate_id=affiliates.id ';
        $selectSQL = 'SELECT affiliate_campaign_requests.id, affiliate_campaign_requests.campaign_id, campaigns.name, affiliate_campaign_requests.affiliate_id, affiliates.company, affiliate_campaign_requests.status, affiliate_campaign_requests.created_at, affiliate_campaign_requests.updated_at
                        FROM affiliate_campaign_requests
                        INNER JOIN affiliates ON affiliate_campaign_requests.affiliate_id = affiliates.id
                        INNER JOIN campaigns ON affiliate_campaign_requests.campaign_id = campaigns.id
                        WHERE affiliate_campaign_requests.status = '.$request_status;
        $whereSQL = '';

        $requestIDAdded = false;

        //where
        if (! empty($param) || $param != '') {
            $whereSQL = ' AND (';

            if (is_numeric($param)) {
                $whereSQL = $whereSQL.'affiliate_campaign_requests.id ='.(int) $param.' OR ';
            }

            $whereSQL = $whereSQL." campaigns.name LIKE '%".$param."%'";
            $whereSQL = $whereSQL.' OR affiliate_campaign_requests.affiliate_id ='.(int) $param;
            $whereSQL = $whereSQL." OR affiliates.company LIKE '%".$param."%' ";

            $status = -1;
            if (strcasecmp($param, 'apply to run') == 0 || stripos('apply to run', $param) !== false) {
                $status = 0;
            } elseif (strcasecmp($param, 'active') == 0 || stripos('active', $param) !== false) {
                $status = 1;
            } elseif (strcasecmp($param, 'pending') == 0 || stripos('pending', $param) !== false) {
                $status = 2;
            } elseif (strcasecmp($param, 'rejected') == 0 || stripos('rejected', $param) !== false) {
                $status = 3;
            }
            if ($status >= 0) {
                $whereSQL = $whereSQL.' OR affiliate_campaign_requests.status='.$status;
            }

            $whereSQL .= ' ) ';
        }

        //$orderSQL = " ORDER BY affiliates.id ASC";
        $orderSQL = ' ORDER BY '.$columns[$inputs['order'][0]['column']].' '.$inputs['order'][0]['dir'];

        $limitSQL = '';
        if ($length > 1) {
            $limitSQL = ' LIMIT '.$start.','.$length;
        }

        $sqlWithLimit = $selectSQL.$whereSQL.$orderSQL.$limitSQL;

        $sqlWithoutLimit = $selectSQL.$whereSQL.$orderSQL;

        //Log::info('SQL with limit: '.$sqlWithLimit);
        //Log::info('SQL without limit: '.$sqlWithoutLimit);

        $requests = DB::select($sqlWithLimit);

        $affiliate_request_statuses = config('constants.AFFILIATE_REQUEST_STATUSES');
        foreach ($requests as $request) {
            $data = [];
            // array_push($data,$request->id);

            $dateUpdatedHTML = '<span id="rqst-'.$request->id.'-dteUp">'.$request->updated_at.'</span>';
            array_push($data, $dateUpdatedHTML);

            //create the date applied
            $dateAppliedHTML = '<span id="rqst-'.$request->id.'-dteAp">'.$request->created_at.'</span>';
            array_push($data, $dateAppliedHTML);

            //create the affiliate
            $affiliateHTML = '<span id="rqst-'.$request->id.'-aff" data-id="'.$request->affiliate_id.'">'.$request->affiliate_id.' - '.$request->company.'</span>';
            array_push($data, $affiliateHTML);

            //create the campaign
            $campaignHTML = '<span id="rqst-'.$request->id.'-cmp" data-id="'.$request->campaign_id.'">'.$request->name.'</span>';
            array_push($data, $campaignHTML);

            // //create the affiliate
            // $statusHTML = '<span id="rqst-'.$request->id.'-stat" data-id="'.$request->status.'">'.$affiliate_request_statuses[$request->status].'</span>';
            // array_push($data,$statusHTML);

            //create contact details html
            if ($request_status == 2) {
                $approveHTML = '<button class="approveRequestBtn btn btn-success" title="Approve" type="button" data-id="'.$request->id.'"><i class="fa fa-check-circle" aria-hidden="true"></i> Approve</button> ';
                $rejectHTML = '<button class="rejectRequestBtn btn btn-danger" title="Reject" type="button" data-id="'.$request->id.'"><i class="fa fa-times-circle" aria-hidden="true"></i> Reject</button>';
                array_push($data, $approveHTML.$rejectHTML);
            } elseif ($request_status == 3) {
                $revertHTML = '<button class="revertRequestBtn btn btn-danger" title="Revert" type="button" data-id="'.$request->id.'"><i class="fa fa-times-circle" aria-hidden="true"></i> Revert</button>';
                array_push($data, $revertHTML);
            } else {
                array_push($data, '');
            }

            array_push($requestsData, $data);
        }

        $totalCount = AffiliateCampaignRequest::where('status', $request_status)->count();

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalCount,  // total number of records
            'recordsFiltered' => $totalCount, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $requestsData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request)
    {
        $id = $request->input('id');

        // Approve
        $affiliate_request = AffiliateCampaignRequest::find($id);
        $affiliate_request->status = 1;
        $affiliate_request->save();

        //Add Affiliate to Campaign's Affiliate tab
        $affCampAlreadyExists = AffiliateCampaign::where('affiliate_id', $affiliate_request->affiliate_id)->where('campaign_id', $affiliate_request->campaign_id)->exists();
        if (! $affCampAlreadyExists) {
            AffiliateCampaign::firstOrCreate([
                'campaign_id' => $affiliate_request->campaign_id,
                'affiliate_id' => $affiliate_request->affiliate_id,
                'lead_cap_type' => 0,
                'lead_cap_value' => 0,
            ]);
        }

        //Email Affiliate
        //Uncomment in live
        $this->sendEmailToAffiliate($affiliate_request->campaign_id, $affiliate_request->affiliate_id, $affiliate_request->status);

        return $id;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request)
    {
        $id = $request->input('id');
        $affiliate_request = AffiliateCampaignRequest::find($id);
        $affiliate_request->status = 3;
        $affiliate_request->save();

        //Email Affiliate
        //Uncomment in live

        $this->sendEmailToAffiliate($affiliate_request->campaign_id, $affiliate_request->affiliate_id, $affiliate_request->status);

        return $id;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function revert(Request $request)
    {
        $id = $request->input('id');
        $affiliate_request = AffiliateCampaignRequest::find($id);
        $affiliate_request->status = 2;
        $affiliate_request->save();

        return $id;
    }

    /**
     * Affiliate requests statuses
     *
     * @return mixed
     */
    public function affiliateRequestStatusList()
    {
        $statuses = config('constants.AFFILIATE_REQUEST_STATUSES');
        $statusesArray = [];

        foreach ($statuses as $key => $value) {
            array_push($statusesArray, ['value' => $key, 'option' => $value]);
        }

        return $statusesArray;
    }

    /**
     * Receive affilite request to run a campaign
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $affiliate_id = $request->input('affiliate');
        $campaign_id = $request->input('campaign');
        $statuses = config('constants.AFFILIATE_REQUEST_STATUSES');

        $affiliateCampaignRequest = AffiliateCampaignRequest::firstOrNew([
            'campaign_id' => $campaign_id,
            'affiliate_id' => $affiliate_id,
        ]);

        if (! $affiliateCampaignRequest->exists() || $affiliateCampaignRequest->status == 0) {
            $affiliateCampaignRequest->status = 2;
            $affiliateCampaignRequest->save();
            //Email Admin //Uncomment in live
            $admins = User::where('account_type', 2)->get();
            $campaign = Campaign::find($campaign_id);
            $affiliate = Affiliate::find($affiliate_id);

            foreach ($admins as $admin) {
                $email_data = [
                    'admin' => $admin,
                    'campaign' => $campaign,
                    'affiliate' => $affiliate,
                ];
                $job = (new AdminApplyToRunRequest($email_data))->delay(5);
                $this->dispatch($job);
            }
            $return = 'Success';
        } else {
            $return = $statuses[$affiliateCampaignRequest->status];
        }

        return $return;
    }

    private function sendEmailToAffiliate($campaign_id, $affiliate_id, $status)
    {
        $campaign = Campaign::find($campaign_id);
        $affiliate = User::where('affiliate_id', $affiliate_id)->first();
        $the_status = $status == 1 ? 'Approved' : 'Rejected';
        $redirect = url('affiliate/campaigns');

        Mail::later(5, 'emails.affiliate_apply_to_run_request',
            ['campaign' => $campaign, 'affiliate' => $affiliate, 'status' => $the_status, 'redirect' => $redirect],
            function ($m) use ($affiliate, $the_status) {

                $m->from('admin@engageiq.com', 'Engage IQ');

                $m->to($affiliate->email, $affiliate->first_name.' '.$affiliate->last_name)
                    ->subject('Engage IQ: Your Request has been '.$the_status);
            });
    }

    /* Posting Instruction */
    public function getCampaignPostingInstruction(Request $request)
    {
        $campaign = $request->input('campaign');
        $affiliate = $request->input('affiliate');
        $affiliate_request = AffiliateCampaignRequest::where('campaign_id', $campaign)
            ->where('affiliate_id', $affiliate)
            ->where('status', 1)
            ->first();

        if ($affiliate_request != null) {
            $campaign = CampaignPostingInstruction::find($campaign);
            if ($campaign != null) {
                $posting_instruction = $campaign->toArray();

                $sample_code = $posting_instruction['sample_code'];
                $sample_code = str_replace('<', '&lt;', $sample_code);
                $sample_code = str_replace('>', '&gt;', $sample_code);

                $posting_instruction['sample_code'] = $sample_code;
            } else {
                $posting_instruction = null;
            }
            $return['status'] = 1;
            $return['data'] = $posting_instruction;
        } else {
            $return['status'] = 0;
            $return['data'] = null;
        }

        return $return;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
