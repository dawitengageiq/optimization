<?php

namespace App\Exceptions;

use Exception;

class CampaignListsResolverException extends Exception
{
    protected $errorID;

    protected $details;

    public function __construct($message)
    {
        $message = $this->create(func_get_args());
        parent::__construct($message);
    }

    protected function create(array $args)
    {
        $this->errorID = array_shift($args);
        $error = $this->errors($this->errorID);
        $this->details = vsprintf($error['context'], $args);

        return $this->details;
    }

    protected function errors($errorID)
    {
        $data = [
            'forbidden' => [
                'context' => trans('campaignList.forbidden'),
            ],
            'invalid_affiliate_id' => [
                'context' => trans('campaignList.invalid_affiliate_id'),
            ],
            'provide_affiliate_id' => [
                'context' => trans('campaignList.provide_affiliate_id'),
            ],
            'provide_website_id' => [
                'context' => trans('campaignList.provide_website_id'),
            ],
            'incomplete_parameters' => [
                'context' => trans('campaignList.incomplete_parameters'),
            ],
        ];

        return $data[$errorID];
    }
}
