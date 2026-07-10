<?php

use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    exit('Access Denied');
}

class SmsSender
{
    protected $baseUrl = 'https://edge.XXXXXXX.com/v1';

    public function sendPattern($apiKey, $patternCode, $mobile, array $variables)
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);

        if (substr($mobile, 0, 1) == '0') {
            $mobile = '98' . substr($mobile, 1);
        }

        if (substr($mobile, 0, 2) != '98') {
            $mobile = '98' . $mobile;
        }

        $mobile = '+' . $mobile;

        $payload = [
            'sending_type' => 'pattern',
            'from_number'  => '+XXXXXXXXXX',
            'code'         => $patternCode,
            'recipients'   => [
                $mobile
            ],
            'params'       => $variables
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->baseUrl . '/api/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . trim($apiKey),
                'Content-Type: application/json',
                'Accept: application/json'

            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $this->writeLog(
            $patternCode,
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            ($response ? $response : $error),
            ($httpCode >= 200 && $httpCode < 300 ? 'success' : 'failed')
        );

        return ($httpCode >= 200 && $httpCode < 300);
    }

    protected function writeLog($event, $request, $response, $status)
    {
        try {

            Capsule::table('mod_securesms_logs')->insert([
                'event_type' => $event,
                'request'    => $request,
                'response'   => $response,
                'status'     => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {

            logActivity(
                'SecureSMS Log Error: ' . $e->getMessage()
            );

        }
    }
}