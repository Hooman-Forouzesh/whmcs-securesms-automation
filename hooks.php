<?php

use WHMCS\Database\Capsule;

require_once __DIR__ . '/lib/Queue.php';

function securesms_get_setting($key)
{
    $addon = Capsule::table('tbladdonmodules')
        ->where('module', 'securesms')
        ->where('setting', $key)
        ->first();

    return $addon ? $addon->value : null;
}

/*
|--------------------------------------------------------------------------
| Admin Login
|--------------------------------------------------------------------------
*/

add_hook('AdminLogin', 1, function ($vars) {

    $adminMobile = securesms_get_setting('admin_mobile');

    Queue::add(
        'admin_login',
        $adminMobile,
        securesms_get_setting('login_pattern'),
        [
            'admin' => $vars['username'] ?? 'Admin',
            'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'time'  => date('Y-m-d H:i:s')
        ]
    );
});

/*
|--------------------------------------------------------------------------
| Invoice Created
|--------------------------------------------------------------------------
*/

add_hook('InvoiceCreated', 1, function ($vars) {

    $invoiceId = (int)$vars['invoiceid'];

    $invoice = Capsule::table('tblinvoices')
        ->where('id', $invoiceId)
        ->first();

    if (!$invoice) {
        return;
    }

    $client = Capsule::table('tblclients')
        ->where('id', $invoice->userid)
        ->first();

    if (!$client) {
        return;
    }

    Queue::add(
        'invoice_created',
        $client->phonenumber,
        securesms_get_setting('invoice_created_pattern'),
        [
            'invoice' => $invoice->id,
            'amount'  => $invoice->total
        ]
    );
});

/*
|--------------------------------------------------------------------------
| Invoice Paid
|--------------------------------------------------------------------------
*/

add_hook('InvoicePaid', 1, function ($vars) {

    $invoiceId = (int)$vars['invoiceid'];

    $invoice = Capsule::table('tblinvoices')
        ->where('id', $invoiceId)
        ->first();

    if (!$invoice) {
        return;
    }

    $client = Capsule::table('tblclients')
        ->where('id', $invoice->userid)
        ->first();

    if (!$client) {
        return;
    }

    Queue::add(
        'invoice_paid',
        $client->phonenumber,
        securesms_get_setting('invoice_paid_pattern'),
        [
            'invoice' => $invoice->id,
            'amount'  => $invoice->total
        ]
    );
});


/*
|--------------------------------------------------------------------------
| Admin New Ticket Notification
|--------------------------------------------------------------------------
*/

add_hook('TicketOpen', 1, function ($vars) {

    $ticketId = (int)$vars['ticketid'];

    $ticket = Capsule::table('tbltickets')
        ->where('id', $ticketId)
        ->first();

    if (!$ticket) {
        return;
    }

    $client = Capsule::table('tblclients')
        ->where('id', $ticket->userid)
        ->first();

    if (!$client) {
        return;
    }

    Queue::add(
        'ticket_open_admin',
        securesms_get_setting('admin_mobile'),
        securesms_get_setting('ticket_open_admin_pattern'),
        [
            'ticket' => $ticket->tid,
            'client' => $client->firstname . ' ' . $client->lastname,
            'subject' => $ticket->subject
        ]
    );

});


/*
|--------------------------------------------------------------------------
| Answer Customer Ticket
|--------------------------------------------------------------------------
*/

add_hook('TicketAdminReply', 1, function ($vars) {

    $ticketId = (int)$vars['ticketid'];

    $ticket = Capsule::table('tbltickets')
        ->where('id', $ticketId)
        ->first();

    if (!$ticket) {
        return;
    }

    $client = Capsule::table('tblclients')
        ->where('id', $ticket->userid)
        ->first();

    if (!$client) {
        return;
    }

    Queue::add(
        'ticket_reply_customer',
        $client->phonenumber,
        securesms_get_setting('ticket_reply_customer_pattern'),
        [
            'ticket' => $ticket->tid,
            'subject' => $ticket->subject
        ]
    );

});

/*
|--------------------------------------------------------------------------
| New User Registration
|--------------------------------------------------------------------------
*/

add_hook('ClientAdd', 1, function ($vars) {

    $clientId = (int)$vars['userid'];

    $client = Capsule::table('tblclients')
        ->where('id', $clientId)
        ->first();

    if (!$client) {
        return;
    }

    Queue::add(
        'user_registration',
        $client->phonenumber,
        securesms_get_setting('user_registration_pattern'),
        [
            'name'  => $client->firstname . ' ' . $client->lastname,
            'email' => $client->email
        ]
    );

});