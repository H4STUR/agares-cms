<?php

use App\Models\Form;
use App\Models\InputInstance;

if (!function_exists('contact_form_from_instance')) {
    function contact_form_from_instance(?InputInstance $instance): ?Form
    {
        if (!$instance) return null;

        $val = $instance->value;
        if (!is_string($val) || trim($val) === '') return null;

        $arr = json_decode($val, true);
        if (!is_array($arr) || empty($arr['form_id'])) return null;

        return Form::with('fields')->find((int)$arr['form_id']);
    }
}
