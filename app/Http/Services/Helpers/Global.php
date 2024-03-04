<?php

/**
 * part of Lead Reactor helper functions, use as is
 * silly helper functions
 */
/**
 * prints nicer outputs from native print_r function
 */
if (! function_exists('printR')) {
    function printR($array, $toArray = false)
    {
        if ($toArray) {
            $array = $array->toArray();
        }

        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}

/**
 * prints Ang Ganda Mo from ang_ganda_mo (string)
 */
if (! function_exists('kabawCase')) {
    function kabawCase($string, $replaceable = '')
    {
        if (! $replaceable) {
            $replaceable = '_';
        }

        return ucwords(str_replace($replaceable, ' ', $string));
    }
}

/**
 * prints angGandaMo from ang_ganda_mo (string)
 */
if (! function_exists('camelCase')) {
    function camelCase($string, $replaceable = '')
    {
        if (! $replaceable) {
            $replaceable = '_';
        }

        $str = str_replace(' ', '', ucwords(str_replace($replaceable, ' ', $string)));

        $str[0] = strtolower($str[0]);

        return $str;
    }
}

/**
 *  removes non-numeric for phone numbers
 */
if (! function_exists('numbersOnly')) {
    function numbersOnly($phone)
    {
        return preg_replace('/[^0-9+]/', '', $phone);
    }
}

/**
 *  removes non-numeric for phone numbers
 */
if (! function_exists('tap')) {
    function tap($value, $callback)
    {
        $callback($value);

        return $value;
    }
}

/**
 *  Dirty fix, only for windows :(
 */
//
// if (!function_exists(' posix_getpwuid')) {
//    function  posix_getpwuid() {
//       return getmypid();
//    }
// }
//
// /**
//  *  Dirty fix, only for windows :(
//  */
// if (!function_exists(' posix_geteuid')) {
//    function  posix_geteuid() {
//       return getmypid();
//    }
// }
