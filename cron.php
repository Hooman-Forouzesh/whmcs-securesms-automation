<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '/home/user/public_html/whmcs/init.php';

require_once __DIR__ . '/lib/SmsSender.php';

use WHMCS\Database\Capsule;

$apiKey = Capsule::table('tbladdonmodules')
    ->where('module', 'securesms')
    ->where('setting', 'api_key')
    ->value('value');


$rows = Capsule::table('mod_securesms_queue')
    ->where('status', 'pending')
    ->limit(50)
    ->get();


$sms = new SmsSender();


foreach ($rows as $row) {

    try {

        $result = $sms->sendPattern(
            $apiKey,
            $row->pattern_code,
            $row->mobile,
            json_decode($row->variables, true)
        );


        Capsule::table('mod_securesms_queue')
            ->where('id', $row->id)
            ->update([
                'status'  => $result ? 'sent' : 'failed',
                'sent_at' => date('Y-m-d H:i:s')
            ]);


    } catch (\Throwable $e) {

        logActivity(
            'SecureSMS Cron Error: ' . $e->getMessage()
        );

    }

}

echo "DONE";