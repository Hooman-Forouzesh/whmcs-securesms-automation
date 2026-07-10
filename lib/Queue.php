<?php

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit;
}

class Queue
{
    public static function add($eventType, $mobile, $patternCode, array $variables)
    {
        return Capsule::table('mod_securesms_queue')->insert([
            'event_type'  => $eventType,
            'mobile'      => $mobile,
            'pattern_code'=> $patternCode,
            'variables'   => json_encode($variables, JSON_UNESCAPED_UNICODE),
            'status'      => 'pending',
            'retry_count' => 0,
            'created_at'  => date('Y-m-d H:i:s')
        ]);
    }
}