<?php

namespace App\Http\Services\Consolidated\Utils\Forms;

use Form;

class DatePicker extends \Illuminate\Html\FormBuilder
{
    public function __construct(\Illuminate\Html\FormBuilder $form)
    {
        // $this->register();
        $this->form = $form;
        printR($form, true);
        parent::__construct(

        );
    }

    /**
     * Register date picker
     */
    public static function register()
    {

        printR($this, true);

        Form::macro('datePicker', function ($name = 'date', $value = null, $options = []) {
            $type = 'text';
            if (! isset($options['name'])) {
                $options['name'] = $name;
            }

            // We will get the appropriate value for the given field. We will look for the
            // value in the session for the value in the old input data then we'll look
            // in the model instance if one is set. Otherwise we will just use empty.
            // $id = $this->getIdAttribute($name, $options);
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

            // Create html
            $html = '<div class="input-group date">
                        <input'.$attributes.'>';
            $html .= '
                        <span class="input-group-addon">
                            <i class="glyphicon glyphicon-th"></i>
                        </span>
                    </div>
                    ';

            return $html;
        });
    }
}
