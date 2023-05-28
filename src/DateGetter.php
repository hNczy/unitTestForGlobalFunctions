<?php

namespace MyNamespace;

class DateGetter
{
    public function get_date($format = 'Y-m-d H:i:s', $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        return date($format, $timestamp);
    }
}
