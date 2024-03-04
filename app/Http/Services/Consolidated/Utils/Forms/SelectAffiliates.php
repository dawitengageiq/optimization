<?php

namespace App\Http\Services\Consolidated\Utils\Forms;

use Form;

class SelectAffiliates
{
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register affiliates selection
     */
    public static function register()
    {
        Form::macro('affiliates', function ($name = 'affiliates', $affiliates = [], $inputs = [], $options = []) {
            $type = 'select';
            if (! isset($options['name'])) {
                $options['name'] = $name;
            }

            // We will get the appropriate value for the given field. We will look for the
            // value in the session for the value in the old input data then we'll look
            // in the model instance if one is set. Otherwise we will just use empty.
            $id = (array_key_exists('id', $options)) ? $options['id'] : $name;

            // Once we have the type, value, and ID we can merge them into the rest of the
            // attributes array so we can convert them into their HTML attribute format
            // when creating the HTML element. Then, we will return the entire input.
            $options = array_merge($options, compact('type', 'value', 'id'));

            $attributes = [];

            // For numeric keys we will assume that the key and the value are the same
            // as this will convert HTML attributes such as "required" to a correct
            // form like required="required" instead of using incorrect numerics.
            foreach ((array) $options as $key => $value) {
                if (is_numeric($key)) {
                    $key = $value;
                }

                $element = null;
                if (! is_null($value)) {
                    $element = $key.'="'.e($value).'"';
                }

                if (! is_null($element)) {
                    $attributes[] = $element;
                }
            }

            $attributes = count($attributes) > 0 ? ' '.implode(' ', $attributes) : '';

            $html = '<select'.$attributes.'>'."\n\r";

            // options
            $html .= \App\Http\Services\Consolidated\Utils\Forms\SelectAffiliates::options($affiliates, $inputs);

            //
            $html .= "\t\t\t\t\t\t".'</select>';

            return $html;
        });
    }

    /**
     * Set affiliates options
     */
    public static function options(array $affiliates, array $inputs): string
    {
        $options = "\t\t\t\t\t\t\t".'<option value="">ALL</option>'."\n\r";

        if (! count($affiliates)) {
            return $options;
        }

        foreach ($affiliates as $affiliate) {
            foreach ($affiliate['revenue_tracker'] as $revenueTracker) {
                $options .= "\t\t\t\t\t\t\t".'<option value="'.$revenueTracker['revenue_tracker_id'].'"';

                if ($inputs['revenue_tracker_id'] == $revenueTracker['revenue_tracker_id']) {
                    $options .= ' selected';
                }

                $options .= '>';

                $options .= $affiliate['company'].' '.$revenueTracker['revenue_tracker_id'].' - '.$revenueTracker['campaign_id'];

                $options .= '</option>'."\n\r";
            }
        }

        return $options;
    }
}
