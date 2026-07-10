<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function securesms_config()
{
    return [
        'name'        => 'Secure SMS',
        'description' => 'IPPanel Pattern SMS for WHMCS 8.12',
        'version'     => '1.0.0',
        'author'      => 'Custom Development',
        'language'    => 'english',
        'fields'      => [

            'api_key' => [
                'FriendlyName' => 'IPPanel API Key',
                'Type'         => 'password',
                'Size'         => '80',
                'Description'  => 'IPPanel API Key'
            ],

            'sender' => [
                'FriendlyName' => 'Sender Number',
                'Type'         => 'text',
                'Size'         => '30',
                'Default'      => '+XXXXXXX'
            ],

            'admin_mobile' => [
                'FriendlyName' => 'Admin Mobile',
                'Type'         => 'text',
                'Size'         => '20',
                'Default'      => '+XXXXXXXXX'
            ],

            'login_pattern' => [
                'FriendlyName' => 'Admin Login Pattern',
                'Type'         => 'text',
                'Size'         => '50',
                'Default'      => 'XXXXXXXXXXXXXX'
            ],

            'invoice_created_pattern' => [
                'FriendlyName' => 'Invoice Created Pattern',
                'Type'         => 'text',
                'Size'         => '50',
                'Default'      => 'XXXXXXXXXXXXXX'
            ],

            'invoice_paid_pattern' => [
                'FriendlyName' => 'Invoice Paid Pattern',
                'Type'         => 'text',
                'Size'         => '50',
                'Default'      => 'XXXXXXXXXXXXXX'
            ],
            
            'ticket_open_customer_pattern' => [
                'FriendlyName' => 'Ticket Open Customer Pattern',
                'Type'         => 'text',
                'Size'         => '50',
                'Default'      => ''
            ],

            'ticket_open_admin_pattern' => [
                 'FriendlyName' => 'Ticket Open Admin Pattern',
                 'Type'         => 'text',
                 'Size'         => '50',
                 'Default'      => ''
            ],
            
            'user_registration_pattern' => [
                 'FriendlyName' => 'New User Registration Pattern',
                 'Type'         => 'text',
                 'Size'         => '50',
                 'Default'      => ''
            ],
        ]
    ];
}

function securesms_activate()
{
    try {

        if (!Capsule::schema()->hasTable('mod_securesms_queue')) {

            Capsule::schema()->create('mod_securesms_queue', function ($table) {

                $table->increments('id');

                $table->string('event_type', 50);

                $table->string('mobile', 30);

                $table->string('pattern_code', 100);

                $table->longText('variables');

                $table->enum('status', [
                    'pending',
                    'sent',
                    'failed'
                ])->default('pending');

                $table->integer('retry_count')->default(0);

                $table->timestamp('created_at')->useCurrent();

                $table->timestamp('sent_at')->nullable();

            });
        }

        if (!Capsule::schema()->hasTable('mod_securesms_logs')) {

            Capsule::schema()->create('mod_securesms_logs', function ($table) {

                $table->increments('id');

                $table->string('event_type', 50);

                $table->text('request')->nullable();

                $table->text('response')->nullable();

                $table->string('status', 20);

                $table->timestamp('created_at')->useCurrent();

            });
        }

        return [
            'status' => 'success',
            'description' => 'SecureSMS activated successfully'
        ];

    } catch (\Exception $e) {

        return [
            'status' => 'error',
            'description' => $e->getMessage()
        ];
    }
}

function securesms_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'SecureSMS deactivated'
    ];
}

function securesms_output($vars)
{
    echo '
    <div class="alert alert-success">
        SecureSMS Module Active
    </div>';
}