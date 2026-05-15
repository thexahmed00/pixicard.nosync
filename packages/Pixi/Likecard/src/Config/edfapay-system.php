<?php
return [
    [ // <-- Add this opening bracket
        'key'  => 'sales.payment_methods.edfapay',
        'name' => 'Edfapay',
        'sort' => 5,
        'info' => 'Configuration settings for the Edfapay payment gateway.',
        'fields' => [
            [
                'name' => 'active',
                'title' => 'Status',
                'type' => 'boolean',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => false,
            ],
            [
                'name'          => 'merchant_id',
                'title'         => 'Merchant ID',
                'type'          => 'text',
                'validation'    => 'required',
            ],
            [
                'name'          => 'merchant_password',
                'title'         => 'Merchant Password',
                'type'          => 'password', // Use 'password' type to obscure the value
                'validation'    => 'required',
            ],
        ],
    ] // <-- Add this closing bracket
];
