<?php

namespace App\Http\Services\Consolidated\Utils\Forms;

use Form;

class SelectLegends
{
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register legends selection.
     */
    public static function register(): string
    {
        Form::macro('legends', function ($name = '', $legends = [], $inputs = [], $options = []) {
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
            $html .= \App\Http\Services\Consolidated\Utils\Forms\SelectLegends::options($legends, $inputs);

            //
            $html .= "\t\t\t\t\t\t".'</select>';

            return $html;
        });
    }

    /**
     * Set legends options
     *
     * @param  array  $affiliates
     */
    public static function options($legends, array $inputs): string
    {
        $options = "\t\t\t\t\t\t\t".'<option value="all"';

        if (count($inputs['legends']) == 1 && $inputs['legends'][0] == 'all') {
            $options .= ' selected';
        }

        $options .= '>ALL</option>'."\n\r";

        if (! count($legends)) {
            return $options;
        }

        foreach ($legends as $legend => $details) {
            $options .= "\t\t\t\t\t\t\t".'<option value="'.$legend.'"';

            if (count($inputs['legends']) > 1 || (count($inputs['legends']) == 1 && $inputs['legends'][0] != 'all')) {
                if (in_array($legend, $inputs['legends'])) {
                    $options .= ' selected';
                }
            }

            $options .= '>';

            $options .= $details['alias'];

            $options .= '</option>'."\n\r";
        }

        return $options;
    }
}
