<?php

namespace App\Http\Requests;

use App\Exceptions\CampaignListsResolverException;
use Illuminate\Foundation\Http\FormRequest;

class CampaignListRequest extends FormRequest
{
    /**
     * Default variables
     */
    protected $limit;

    protected $settings;

    protected $limitType = 'No';

    protected $orderType = 'default';

    protected $stackType = 'default';

    protected $filter = true;

    protected $incomplete = false;

    protected $required = [
        'first_name',
        'last_name',
        'email',
        'zip',
        'dobmonth',
        'dobday',
        'dobyear',
        'gender',
    ];

    protected $userDetails = [
        'screen_view' => '1',
        'submitBtn' => 'Submit',
        'submit' => 'engageiq_post_data',
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'email2' => '',
        'zip' => '',
        'birthdate' => '',
        'dobmonth' => '',
        'dobday' => '',
        'dobyear' => '',
        'gender' => '',
        'address' => '',
        'user_agent' => '',
        'phone' => '',
        'phone1' => '',
        'phone2' => '',
        'phone3' => '',
        'ethnicity' => '',
        'source_url' => '',
        'image' => '',
        'affiliate_id' => '',
        'offer_id' => '',
        'campaign_id' => '',
        's1' => '',
        's2' => '',
        's3' => '',
        's4' => '',
        's5' => '',
        'cs1' => '',
        'cs2' => '',
        'cs3' => '',
        'cs4' => '',
        'cs5' => '',
        'ip' => '',
        'filter_questions' => [],
    ];

    /**
     * Empty the rules for we are not using the default validation
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request.
     * Since its a pre process, process some functions.
     */
    public function authorize()
    {
        /*if ($this->incompleteRequestData()) {
            //return false;
            throw new CampaignListsResolverException('incomplete_parameters');
        }*/

        if ($this->has('filter')) {
            $this->filter = $this->get('filter');
        }
        $this->limit = $this->get('limit');

        $this->setProperties();

        $this->filterUserdata();

        return true;
    }

    /**
     * Retrieve user details
     */
    public function userDetails(): array
    {
        return $this->userDetails;
    }

    /**
     * Limit details
     */
    public function limit()
    {
        return $this->limit;
    }

    /**
     * Limit details
     */
    public function limitType()
    {
        return $this->limitType;
    }

    /**
     * Limit details
     */
    public function orderType()
    {
        return $this->orderType;
    }

    /**
     * Limit details
     */
    public function stackType()
    {
        return $this->stackType;
    }

    /**
     * filter details
     */
    public function filter()
    {
        return $this->filter;
    }

    protected function setProperties()
    {
        // Save to global variable
        if ($this->has('limit_type')) {
            $this->limitType = str_replace('_', ' ', ucwords($this->get('limit_type')));
        }
        if ($this->has('order_type')) {
            $this->orderType = str_replace('_', ' ', ucwords($this->get('order_type')));
        }
        if ($this->has('stack_type')) {
            $this->stackType = str_replace('_', ' ', ucwords($this->get('stack_type')));
        }
    }

    /**
     * Update user details with request data
     *
     * @var array
     */
    protected function filterUserdata()
    {
        $this->userDetails = collect($this->userDetails)
            ->map(function ($detail, $key) {
                return ($this->has($key)) ? $this->sanitize($this->get($key)) : $detail;
            })->toArray();
    }

    /**
     * SAnitize data from php injection,
     * when showing offers, we use the php pre define function:eval().
     *
     * @method sanitize
     */
    protected function sanitize($data)
    {
        if (! is_array($data)) {
            $data = preg_replace('[ \t\r]', '', $data);
            $data = strip_tags($data);

            // Pecautions
            $data = preg_replace('#<?php.*?>#m', '', $data);

            // Precautions
            $data = str_replace('<?php', '', $data);
            $data = str_replace('<?=', '', $data);
            $data = str_replace('<?', '', $data);
            $data = str_replace('?>', '', $data);
            $data = str_replace('$', '&#36;', $data);
            $data = str_replace('=', '&#61;', $data);
            $data = str_replace('"', '&#34;', $data);
            $data = str_replace('\\', '&#92;', $data);
        }

        return $data;
    }

    /**
     * Check request data is complete.
     */
    protected function incompleteRequestData(): bolean
    {
       
        collect($this->required)->each(function ($required) {
            if (! $this->get($required)) {
                $this->incomplete = true;

                return false;
            }
        });

        return $this->incomplete;
    }
}
