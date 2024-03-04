<?php

namespace App\Http\Middleware;

use App\Commands\GetUserActionPermission;
use Bus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestrictSectionAccessIfNotPermitted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $path = $request->path();
        $user = $request->user();

        $accessCode = '';

        //check if user is allowed to visit the section pages if not then show view that informs them that they do not have permission
        switch ($path) {
            //case 'affiliate/dashboard':
            case 'admin/dashboard':
                //case 'advertiser/dashboard':
                $accessCode = 'access_dashboard';
                break;

            case 'admin/contacts':
                $accessCode = 'access_contacts';
                break;

            case 'admin/affiliates':
                $accessCode = 'access_affiliates';
                break;

            case 'admin/campaigns':
                //case 'advertiser/campaigns':
                //case 'affiliate/campaigns':
                $accessCode = 'access_campaigns';
                break;

            case 'admin/advertisers':
                $accessCode = 'access_advertisers';
                break;

            case 'admin/filtertypes':
                $accessCode = 'access_filter_types';
                break;

            case 'admin/searchLeads':
                //case 'advertiser/searchLeads':
                //case 'affiliate/searchLeads':
                $accessCode = 'access_search_leads';
                break;

            case 'admin/revenueStatistics':
                //case 'advertiser/revenueStatistics':
                //case 'affiliate/revenueStatistics':
                $accessCode = 'access_revenue_statistics';
                break;

            case 'admin/duplicateLeads':
                break;

            case 'admin/revenuetrackers':
                $accessCode = 'access_revenue_trackers';
                break;

            case 'admin/gallery':
                $accessCode = 'access_gallery';
                break;

            case 'admin/zip_master':
                $accessCode = 'access_zip_master';
                break;

            case 'admin/categories':
                $accessCode = 'access_categories';
                break;

                //apply to run
            case 'admin/affiliate_requests':
                $accessCode = 'access_apply_to_run_request';
                break;

            case 'admin/survey_takers':
                $accessCode = 'access_survey_takers';
                break;

                //Check if user is permitted in Access Users and Roles
            case 'admin/role_management':
            case 'admin/user_management':
                $accessCode = 'access_users_and_roles';
                break;

            case 'admin/settings':
                $accessCode = 'access_settings';
                break;

            case 'admin/survey_paths':
                $accessCode = 'access_survey_paths';
                break;

            case 'admin/prepopStatistics':
                $accessCode = 'access_prepop_statistics';
                break;

            default:

        }

        //check if user is permitted to this page
        if (! empty($accessCode) && ! Bus::dispatch(new GetUserActionPermission($user, $accessCode))) {
            return new Response(view('errors.not_permitted'), 401);
        }

        return $next($request);
    }
}
