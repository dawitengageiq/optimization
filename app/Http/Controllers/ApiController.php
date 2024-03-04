<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\Campaign;
use App\Lead;
use App\LeadDataCsv;
use App\PageView;
use App\PageViewStatistics;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Faker\Factory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Log;
use PHPEncryptData\Simple;

class ApiController extends Controller
{
    /**
     * API for getting the API token
     */
    public function getMyToken(Request $request): JsonResponse
    {
        $isAuthorized = false;

        $userEmail = $request->header('useremail');
        $userPassword = $request->header('userpassword');

        //for encryption and decryption
        $encryptionKey = env('ENCRYPTION_KEY');
        $macKey = env('MAC_KEY');
        $encryptionApplied = env('ENCRYPTION_APPLIED', false);
        $encryptor = new Simple($encryptionKey, $macKey);

        //decrypt if needed the encrypted useremail and password
        if ($encryptionApplied) {
            try {
                $userEmail = $encryptor->decrypt($userEmail);
                $userPassword = $encryptor->decrypt($userPassword);
            } catch (Exception $e) {
                $responseMessage = [
                    'token' => null,
                    'message' => 'unauthorized',
                ];

                //Un encrypted credentials
                return response()->json($responseMessage, 200);
            }

        }

        $user = null;

        if (Auth::once(['email' => $userEmail, 'password' => $userPassword])) {
            //this means user is authenticated user now check it the user has api access
            $user = User::where('email', '=', $userEmail)->first();

            if ($user->role && $user->role->actions) {
                foreach ($user->role->actions as $action) {
                    if ($action->code == 'use_api_access' && $action->pivot->permitted == 1) {
                        $isAuthorized = true;
                    }
                }
            }
        }

        $responseMessage = [
            'token' => null,
            'message' => 'unauthorized',
        ];

        if (! $isAuthorized) {
            // user doesn't exist
            return response()->json($responseMessage, 200);
        } else {

            /*
            //get the id of the user
            $encryptedToken = Crypt::encrypt($userEmail.' '.$user->id);

            $responseMessage = [
                'token' => $encryptedToken,
                'message' => 'success'
            ];

            try
            {
                $decrypted = Crypt::decrypt($encryptedToken);
                Log::info($decrypted);
            }
            catch (DecryptException $e)
            {
                Log::info($e->getMessage());
            }
            */

            $dateNow = Carbon::now()->toDateTimeString();
            $encryptedToken = $encryptor->encrypt("$userEmail $user->id $dateNow");
            $decryptedToken = $encryptor->decrypt($encryptedToken);
            // Log::info("decryptedToken: $decryptedToken");

            $responseMessage = [
                'token' => $encryptedToken,
                'message' => 'success',
            ];

            return response()->json($responseMessage, 200);
        }
    }

    /**
     * Log Page Views
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function logPageView(Request $request): JsonResponse
    {
        $inputs = $request->all();

        if (! isset($inputs['affiliate_id']) || empty($inputs['affiliate_id'])) {
            return response()->json(['message' => 'missing or invalid affiliate_id'], 422);
        }

        if (! isset($inputs['revenue_tracker_id']) || empty($inputs['revenue_tracker_id'])) {
            return response()->json(['message' => 'missing or invalid revenue_tracker_id'], 422);
        }

        if (! isset($inputs['sub_id']) || empty($inputs['sub_id'])) {
            return response()->json(['message' => 'missing or invalid sub_id'], 422);
        }

        $queryResponseMessage = [
            'message' => 'success',
        ];

        $statusCode = 200;

        try {
            $affiliateID = $inputs['affiliate_id'];
            $revenueTracker = $inputs['revenue_tracker_id'];
            $subID = strtolower($inputs['sub_id']);
            $createAt = Carbon::now()->toDateString();

            // sub ids
            $s1 = isset($inputs['s1']) ? $inputs['s1'] : '';
            $s2 = isset($inputs['s2']) ? $inputs['s2'] : '';
            $s3 = isset($inputs['s3']) ? $inputs['s3'] : '';
            $s4 = isset($inputs['s4']) ? $inputs['s4'] : '';
            $s5 = isset($inputs['s5']) ? $inputs['s5'] : '';

            $view = PageView::create([
                'affiliate_id' => $affiliateID,
                'revenue_tracker_id' => $revenueTracker,
                's1' => $s1,
                's2' => $s2,
                's3' => $s3,
                's4' => $s4,
                's5' => $s5,
                'created_at' => $createAt,
                'type' => $subID,
            ]);

            // $pageViewStats = PageViewStatistics::firstOrNew([
            //     'affiliate_id' => $affiliateID,
            //     'revenue_tracker_id' => $revenueTracker,
            //     's1' => $s1,
            //     's2' => $s2,
            //     's3' => $s3,
            //     's4' => $s4,
            //     's5' => $s5,
            //     'created_at' => $createAt
            // ]);
            // switch($subID)
            // {
            //     case 'lp':
            //     case 'LP':
            //         $pageViewStats->lp = $pageViewStats->lp + 1;
            //         break;

            //     case 'rp':
            //     case 'RP':
            //         $pageViewStats->rp = $pageViewStats->rp + 1;
            //         break;

            //     case 'to1':
            //     case 'TO1':
            //         $pageViewStats->to1 = $pageViewStats->to1 + 1;
            //         break;

            //     case 'to2':
            //     case 'TO2':
            //         $pageViewStats->to2 = $pageViewStats->to2 + 1;
            //         break;

            //     case 'mo1':
            //     case 'MO1':
            //         $pageViewStats->mo1 = $pageViewStats->mo1 + 1;
            //         break;

            //     case 'mo2':
            //     case 'MO2':
            //         $pageViewStats->mo2 = $pageViewStats->mo2 + 1;
            //         break;

            //     case 'mo3':
            //     case 'MO3':
            //         $pageViewStats->mo3 = $pageViewStats->mo3 + 1;
            //         break;

            //     case 'mo4':
            //     case 'MO4':
            //         $pageViewStats->mo4 = $pageViewStats->mo4 + 1;
            //         break;

            //     case 'lfc1':
            //     case 'LFC1':
            //         $pageViewStats->lfc1 = $pageViewStats->lfc1 + 1;
            //         break;

            //     case 'lfc2':
            //     case 'LFC2':
            //         $pageViewStats->lfc2 = $pageViewStats->lfc2 + 1;
            //         break;

            //     case 'tbr1':
            //     case 'TBR1':
            //         $pageViewStats->tbr1 = $pageViewStats->tbr1 + 1;
            //         break;

            //     case 'pd':
            //     case 'PD':
            //         $pageViewStats->pd = $pageViewStats->pd + 1;
            //         break;

            //     case 'tbr2':
            //     case 'TBR2':
            //         $pageViewStats->tbr2 = $pageViewStats->tbr2 + 1;
            //         break;

            //     case 'iff':
            //     case 'IFF':
            //         $pageViewStats->iff = $pageViewStats->iff + 1;
            //         break;

            //     case 'rex':
            //     case 'REX':
            //         $pageViewStats->rex = $pageViewStats->rex + 1;
            //         break;

            //     case 'cpawall':
            //     case 'CPAWALL':
            //         $pageViewStats->cpawall = $pageViewStats->cpawall + 1;
            //         break;

            //     case 'exitpage':
            //     case 'EXITPAGE':
            //         $pageViewStats->exitpage = $pageViewStats->exitpage + 1;
            //         break;
            // }

            // $pageViewStats->save();
        } catch (\PDOException $e) {
            $code = $e->getCode();
            $errorMessage = $e->getMessage();
            $trace = $e->getTraceAsString();

            $statusCode = 422;

            $queryResponseMessage['code'] = $code;
            $queryResponseMessage['message'] = $errorMessage;
            $queryResponseMessage['trace'] = $trace;
        }

        return response()->json($queryResponseMessage, $statusCode);
    }

    public function removeDuplicateLeads()
    {
        $duplicatedCampaignEmailSQL = 'SELECT campaign_id, lead_email, count(id) AS count FROM leads GROUP BY campaign_id, lead_email HAVING count(id) > 1 ORDER BY campaign_id';
        $duplicates = DB::select($duplicatedCampaignEmailSQL);

        $numberOfDeleted = 0;

        foreach ($duplicates as $duplicate) {
            $campaignID = $duplicate->campaign_id;
            $leadEmail = $duplicate->lead_email;

            $campaignEmailLeads = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail])->get();

            //get the successful lead to to remain
            $firstSuccess = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 1])->first();
            $firstPending = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 3])->first();
            $firstRejected = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 2])->first();
            $firstFail = Lead::campaignEmailLeads(['campaign_id' => $campaignID, 'lead_email' => $leadEmail, 'lead_status' => 0])->first();

            if ($firstSuccess) {
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstSuccess, $campaignEmailLeads);
            } elseif ($firstPending) {
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstPending, $campaignEmailLeads);
            } elseif ($firstRejected) {
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstRejected, $campaignEmailLeads);
            } elseif ($firstFail) {
                $numberOfDeleted = $numberOfDeleted + $this->removeExceptOne($firstFail, $campaignEmailLeads);
            }

        }

        return ['delete_count' => $numberOfDeleted];
    }

    public function removeExceptOne($leadRemain, $leads)
    {
        $count = 0;

        foreach ($leads as $lead) {
            if ($lead->id != $leadRemain->id) {
                $lead->delete();
                $count++;
            }
        }

        return $count;
    }

    public function generateLeadURLs()
    {
        $faker = Factory::create();

        $campaignIDs = Campaign::pluck('id')->toArray();
        $affiliateIDs = Affiliate::pluck('id')->toArray();
        $emails = User::pluck('email')->toArray();

        $urls = [];

        for ($i = 0; $i < 2; $i++) {
            $affiliateID = $faker->randomElement($affiliateIDs);
            //$affiliateID = 8;

            for ($j = 0; $j < 4; $j++) {
                //$campaignID = $faker->randomElement($campaignIDs);
                $campaignID = 2;
                //$email = $faker->randomElement($emails);
                $email = $faker->email();

                //$url = 'http://localhost:8000/sendLead?&eiq_campaign_id='.$campaignID.'&eiq_affiliate_id='.$affiliateID.'&eiq_email='.$email.'&rev_tracker=CD7820&program_name=Quick+Attorney+SS+(1072)&add_code=CD7820&program_id=1072&email=ronniegee57%40live.com&firstname=Ron&lastname=Gagon&lr=new&first_name=Ron&last_name=Gagon&dob=08-15-1957&ip=71.199.11.56&datetime=07%2F21%2F2016+02%3A21%3A49&city=American+Fork&state=UT&zip=84003&yesnoto=&quick_attorney_search-campaign=YES&phone=(707)+292-8024&address=14+N+900+E&q1=0&q2=0&q3=0&q4=0&q5=1&comment2=I+tried+previously+to+fill+out+the+papers+to+no+avail.&xxTrustedFormToken=https%3A%2F%2Fcert.trustedform.com%2F181acc04b339bcfca07d0862d65c6a53fe63c894&xxTrustedFormCertUrl=https%3A%2F%2Fcert.trustedform.com%2F181acc04b339bcfca07d0862d65c6a53fe63c894&_=1469082109508';
                $url = url('').'/sendLead?&eiq_campaign_id='.$campaignID.'&eiq_affiliate_id='.$affiliateID.'&eiq_email='.$email.'&rev_tracker=CD7820&program_name=Quick+Attorney+SS+(1072)&add_code=CD7820&program_id=1072&email=ronniegee57%40live.com&firstname=Ron&lastname=Gagon&lr=new&first_name=Ron&last_name=Gagon&dob=08-15-1957&ip=71.199.11.56&datetime=07%2F21%2F2016+02%3A21%3A49&city=American+Fork&state=UT&zip=84003&yesnoto=&quick_attorney_search-campaign=YES&phone=(707)+292-8024&address=14+N+900+E&q1=0&q2=0&q3=0&q4=0&q5=1&comment2=I+tried+previously+to+fill+out+the+papers+to+no+avail.&xxTrustedFormToken=https%3A%2F%2Fcert.trustedform.com%2F181acc04b339bcfca07d0862d65c6a53fe63c894&xxTrustedFormCertUrl=https%3A%2F%2Fcert.trustedform.com%2F181acc04b339bcfca07d0862d65c6a53fe63c894&_=1469082109508';

                //real time
                //$url = url('').'/sendLead?eiq_realtime=1&eiq_campaign_id='.$campaignID.'&eiq_affiliate_id='.$affiliateID.'&eiq_email='.$email.'&rev_tracker=CD7820&program_name=Quick+Attorney+SS+(1072)&add_code=CD7820&program_id=1072&email=ronniegee57%40live.com&firstname=Ron&lastname=Gagon&lr=new&first_name=Ron&last_name=Gagon&dob=08-15-1957&ip=71.199.11.56&datetime=07%2F21%2F2016+02%3A21%3A49&city=American+Fork&state=UT&zip=84003&yesnoto=&quick_attorney_search-campaign=YES&phone=(707)+292-8024&address=14+N+900+E&q1=0&q2=0&q3=0&q4=0&q5=1&comment2=I+tried+previously+to+fill+out+the+papers+to+no+avail.&xxTrustedFormToken=https%3A%2F%2Fcert.trustedform.com%2F181acc04b339bcfca07d0862d65c6a53fe63c894&xxTrustedFormCertUrl=https%3A%2F%2Fcert.trustedform.com%2F181acc04b339bcfca07d0862d65c6a53fe63c894&_=1469082109508';

                array_push($urls, $url);
            }
        }

        return $urls;
    }

    public function test()
    {
        $lead = new Lead;
        $lead->campaign_id = 56;
        $lead->affiliate_id = 78;
        $lead->lead_email = 'asdfsdfgh@amazing.com';
        $lead->save();

        $leadDataCSV = new LeadDataCsv;
        $leadDataCSV->value = '{awts:amazing_awts}';
        $lead->leadDataCSV()->save($leadDataCSV);
    }
}
